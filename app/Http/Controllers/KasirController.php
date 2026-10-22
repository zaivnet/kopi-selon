<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Stok;
use App\Models\User;
use App\Models\Shift;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\Catatan;
use App\Models\Kategori;
use App\Models\Validasi;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ValidasiRetur;
use App\Models\StokTokoKeluar;
use App\Models\DetailTransaksi;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class KasirController extends Controller
{
   public function getShifts($session)
   {
      $tenantId = $session;
      $shifts = Shift::where('tenant_id', $tenantId)->get();
      return $shifts;
      // dd($tenantId, $shifts);
   }

   function tanggalShiftAktif($shift)
   {
      $jamMulai   = Carbon::parse($shift->start_time);
      $jamSelesai = Carbon::parse($shift->end_time);

      if ($jamMulai->gt($jamSelesai)) {
         return Carbon::parse($shift->created_at)->format('d-m-Y');
      }

      return Carbon::now()->format('d-m-Y');
   }

   function tanggalShift($shift)
   {
      $jamMulai   = Carbon::parse($shift->start_time);
      $jamSelesai = Carbon::parse($shift->end_time);

      // shift lintas hari (contoh: 18:00 - 02:00)
      if ($jamMulai->gt($jamSelesai)) {

         $start = Carbon::parse($shift->created_at)
            ->setTimeFrom($jamMulai);

         $end = Carbon::parse($shift->created_at)
            ->addDay()
            ->setTimeFrom($jamSelesai);

         return [
            'start' => $start,
            'end'   => $end,
         ];
      }

      // shift normal (contoh: 08:00 - 16:00)
      $start = Carbon::today()->setTimeFrom($jamMulai);
      $end   = Carbon::today()->setTimeFrom($jamSelesai);

      return [
         'start' => $start,
         'end'   => $end,
      ];
   }

   public static function getRange(int $shiftId, int $tenantId): array
   {
      $shift = Shift::where('tenant_id', $tenantId)
         ->where('id', $shiftId)
         ->firstOrFail();
      $nama_shift = $shift->shift;

      $now = Carbon::now();
      $resetJam = 6; // reset hari toko jam 06:00

      $jamMulai   = Carbon::createFromTimeString($shift->start_time);
      $jamSelesai = Carbon::createFromTimeString($shift->end_time);

      // =========================
      // DETEKSI LINTAS HARI
      // =========================
      $isLintasHari = $shift->start_time > $shift->end_time;

      // =========================
      // TANGGAL SHIFT
      // =========================
      if ($isLintasHari) {

         if ($now->hour < $resetJam) {
            /**
             * 00:00 – 05:59
             * masih bagian shift malam H-1
             */
            $tanggalShift = $now->copy()->subDay();
         } elseif ($now->format('H:i') >= $shift->start_time) {
            /**
             * 22:00 – 23:59
             * shift malam hari ini
             */
            $tanggalShift = $now->copy();
         } else {
            /**
             * 06:00 – 21:59
             * shift malam belum mulai → ambil H-1
             */
            $tanggalShift = $now->copy()->subDay();
         }
      } else {
         // shift normal
         $tanggalShift = $now->copy();
      }

      // =========================
      // RANGE WAKTU SHIFT
      // =========================
      $start = $tanggalShift->copy()
         ->setTimeFromTimeString($shift->start_time);

      if ($isLintasHari) {
         $end = $tanggalShift->copy()
            ->addDay()
            ->setTimeFromTimeString($shift->end_time);
      } else {
         $end = $tanggalShift->copy()
            ->setTimeFromTimeString($shift->end_time);
      }

      // dd($tanggalOperasional, $tanggalShift);

      return [$start, $end, $tanggalShift, $nama_shift];
   }

   public static function dropdownShifts(int $tenantId): array
   {
      $now = Carbon::now();

      $shifts = Shift::where('tenant_id', $tenantId)
         ->orderBy('start_time')
         ->get();

      $result = [];

      foreach ($shifts as $shift) {

         $isLintasHari = $shift->start_time > $shift->end_time;

         if ($isLintasHari) {

            if ($now->format('H:i') >= $shift->start_time) {
               // shift malam hari ini sudah mulai
               $tanggalShift = $now->copy();
               $disabled = false;
            } else {
               // shift malam kemarin (masih boleh dicetak)
               $tanggalShift = $now->copy()->subDay();
               $disabled = false;
            }
         } else {
            // shift normal (pagi / siang)
            $tanggalShift = $now->copy();
            $disabled = $now->format('H:i') < $shift->start_time;
         }

         $result[] = [
            'shift_id'      => $shift->id,
            'tanggal_shift' => $tanggalShift->toDateString(),
            'label'         => "{$shift->shift} ({$tanggalShift->translatedFormat('d M')})",
            'disabled'      => $disabled,
         ];
         // dd($shift['shift']);
      }

      return $result;
   }


   public function index(Request $request)
   {

      $tenantId = session('active_tenant_id');
      $getShifts = $this->dropdownShifts($tenantId);

      if (!$tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih']);
      }

      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      // ===============================
      // 1️⃣ AMBIL SHIFT AKTIF
      // ===============================
      $now      = Carbon::now();
      $timeNow = $now->format('H:i:s');

      $shiftAktif = Shift::where('tenant_id', $tenantId)
         ->where(function ($q) use ($timeNow) {

            // =========================
            // SHIFT NORMAL
            // =========================
            $q->where(function ($qq) use ($timeNow) {
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->where('start_time', '<=', $timeNow)
                  ->where('end_time', '>=', $timeNow);
            })

               // =========================
               // SHIFT MALAM (LINTAS HARI)
               // =========================
               ->orWhere(function ($qq) use ($timeNow) {
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qqq) use ($timeNow) {
                        $qqq->where('start_time', '<=', $timeNow)
                           ->orWhere('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();

      $allShifts = Shift::where('tenant_id', $tenantId)->get();

      if (!$allShifts || !$shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }

      // ===============================
      // 2️⃣ TENTUKAN RANGE SHIFT
      // ===============================
      $now = Carbon::now();

      if ($shiftAktif->start_time > $shiftAktif->end_time) {
         // shift malam
         if ($now->format('H:i:s') < $shiftAktif->end_time) {
            // setelah tengah malam → masih shift kemarin
            $tanggalShift = Carbon::yesterday();
         } else {
            // sebelum tengah malam
            $tanggalShift = Carbon::today();
         }
      } else {
         // shift normal
         $tanggalShift = Carbon::today();
      }

      if ($shiftAktif->start_time < $shiftAktif->end_time) {

         // =========================
         // SHIFT NORMAL
         // =========================
         $startShift = $tanggalShift->copy()
            ->setTimeFromTimeString($shiftAktif->start_time);

         $endShift = $tanggalShift->copy()
            ->setTimeFromTimeString($shiftAktif->end_time);
      } else {

         // =========================
         // SHIFT MALAM (LINTAS HARI)
         // =========================
         $startShift = $tanggalShift->copy()
            ->setTimeFromTimeString($shiftAktif->start_time);

         $endShift = $tanggalShift->copy()
            ->addDay()
            ->setTimeFromTimeString($shiftAktif->end_time);
      }

      // dd($startShift, $endShift);


      // ===============================
      // 3️⃣ TRANSAKSI SHIFT
      // ===============================
      $transaksiShift = Transaksi::where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$startShift, $endShift])
         ->where('status', 'selesai')
         ->get();

      // ===============================
      // 4️⃣ TOTAL PRODUK TERJUAL
      // ===============================
      $totalProdukTerjual = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->sum('detail_transaksis.jumlah');

      // ===============================
      // 5️⃣ PENDAPATAN
      // ===============================
      $pendapatan = $transaksiShift->sum('total_harga');

      // ===============================
      // 6️⃣ PRODUK RETUR
      // ===============================
      $produkRetur = StokTokoKeluar::where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$startShift, $endShift])
         ->get();

      $totalProdukRetur = $produkRetur->sum('jumlah_retur');
      $totalHargaRetur  = $produkRetur->sum(fn($r) => $r->jumlah_retur * $r->harga_produk);
      // dd($produkRetur);
      // ===============================
      // 7️⃣ PENGELUARAN
      // ===============================
      $pengeluaran = Pengeluaran::where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$startShift, $endShift])
         ->get();

      $totalPengeluaran = $pengeluaran->sum('jumlah');
      // dd($totalPengeluaran);

      // ===============================
      // 8️⃣ LABA
      // ===============================
      $laba = $pendapatan - $totalHargaRetur - $totalPengeluaran;

      // ===============================
      // 9️⃣ PRODUK TERLARIS
      // ===============================
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->select(
            'produk.id',
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            DB::raw('SUM(detail_transaksis.jumlah) as total_terjual')
         )
         ->groupBy(
            'produk.id',
            'produk.nama_produk',
            'produk_varian.nama'
         )
         ->orderByDesc('total_terjual')
         ->limit(10)
         ->get();


      // ===============================
      // 🔟 AKTIVITAS TRANSAKSI
      // ===============================
      $aktivitasTransaksi = Transaksi::select(
         'transaksis.*',
         'users.name as nama_kasir'
      )
         ->join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->latest('transaksis.created_at')
         ->limit(10)
         ->get();

      $title = 'Dashboard Kasir';

      // ===============================
      // 1️⃣1️⃣ RETURN KE VIEW
      // ===============================
      return view('pages.kasir.index', compact(
         'getShifts',
         'tenant',
         'title',
         'shiftAktif',
         'tanggalShift',
         'startShift',
         'endShift',
         'totalProdukTerjual',
         'pendapatan',
         'totalProdukRetur',
         'totalHargaRetur',
         'pengeluaran',
         'totalPengeluaran',
         'laba',
         'produkTerlaris',
         'aktivitasTransaksi'
      ));
   }

   public function transaksi()
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $getShifts = $this->dropdownShifts($tenantId);

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      $now        = Carbon::now();
      $timeNow   = $now->format('H:i:s');
      $today     = $now->toDateString();

      $shiftAktif = Shift::where('tenant_id', $tenant->id)
         ->where(function ($q) use ($timeNow) {

            // =========================
            // SHIFT NORMAL (tidak lintas hari)
            // =========================
            $q->where(function ($qq) use ($timeNow) {
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->where('start_time', '<=', $timeNow)
                  ->where('end_time', '>=', $timeNow);
            })

               // =========================
               // SHIFT MALAM (lintas hari)
               // =========================
               ->orWhere(function ($qq) use ($timeNow) {
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qqq) use ($timeNow) {
                        $qqq->where('start_time', '<=', $timeNow)
                           ->orWhere('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();

      if (! $shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }

      // 2️⃣ Ambil produk yang DISPLAY = 1 di toko ini
      $produk = $tenant->produk()
         ->wherePivot('display', 1)
         ->with(['kategori', 'gudang'])
         ->get()
         ->map(function ($item) {
            // Normalisasi data untuk kasir
            return (object) [
               'id'          => $item->id,
               'nama_produk' => $item->nama_produk,
               'kategori'    => $item->kategori->nama_kategori ?? '-',
               'harga_jual'  => $item->pivot->harga_jual,
               'stok'        => $item->pivot->stok,
               'gudang'      => $item->gudang->name ?? '-',
            ];
         });

      // dd($produk);
      $getKategori = Kategori::whereHas('produk.produkToko', function ($q) use ($tenantId) {
         $q->where('tenant_id', $tenantId);
      })
         ->orderBy('nama_kategori')
         ->get();
      $getPelanggan = Pelanggan::all();

      return view('pages.kasir.transaksi.index', [
         'title'      => 'Transaksi',
         'shiftAktif' => $shiftAktif,
         'findTenant' => $tenant,
         'produk'     => $produk,
         'kategori'   => $getKategori,
         'pelanggan' => $getPelanggan,
         'nama_kasir' => auth()->user()->name,
         'tenant' => $tenant,
         'getShifts'  => $getShifts
      ]);
   }

   public function historiTransaksi(Request $request)
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $getShifts = $this->dropdownShifts($tenantId);

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      // =========================
      // 2️⃣ WAKTU SAAT INI
      // =========================
      $now        = Carbon::now();
      $timeNow   = $now->format('H:i:s');
      $today     = $now->toDateString();
      $yesterday = $now->copy()->subDay()->toDateString();

      // =========================
      // 3️⃣ SHIFT AKTIF (BERDASARKAN TENANT)
      // =========================
      $shiftAktif = Shift::where('tenant_id', $tenant->id)
         ->where(function ($q) use ($timeNow) {

            // SHIFT NORMAL
            $q->where(function ($qq) use ($timeNow) {
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->whereTime('start_time', '<=', $timeNow)
                  ->whereTime('end_time', '>=', $timeNow);
            })

               // SHIFT MALAM (LINTAS HARI)
               ->orWhere(function ($qq) use ($timeNow) {
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qx) use ($timeNow) {
                        $qx->whereTime('start_time', '<=', $timeNow)
                           ->orWhereTime('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();


      // dd($shiftAktif);

      if (! $shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }
      // =========================
      // 4️⃣ QUERY TRANSAKSI (DINAMIS)
      // =========================
      [$startShift, $endShift, $tanggalShift, $namaShift] =
         $this->getRange($shiftAktif->id, $tenant->id);

      $metodePembayaran = $request->filled('metode_pembayaran')
         ? $request->metode_pembayaran
         : 'all';

      $getTransaksi = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->select(
            'transaksis.id as id_transaksi',
            'transaksis.*',
            'users.name as nama_kasir'
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->when($metodePembayaran !== 'all', function ($q) use ($metodePembayaran) {
            $q->where('transaksis.metode_pembayaran', $metodePembayaran);
         })
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->orderByDesc('transaksis.id')
         ->get();

      // dd($getTransaksi);


      // =========================
      // 6️⃣ PRODUK TERLARIS (SHIFT AKTIF)
      // =========================
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->select(
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            DB::raw('SUM(detail_transaksis.jumlah) AS total_terjual'),
            DB::raw('SUM(detail_transaksis.total_harga_produk) AS total_penjualan')
         )
         ->groupBy('produk.nama_produk', 'produk_varian.nama')
         ->orderByDesc('total_terjual')
         ->get();


      // =========================
      // 7️⃣ REKAP TRANSAKSI (SHIFT AKTIF)
      // =========================
      $rekapTransaksi = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->when($metodePembayaran !== 'all', function ($q) use ($metodePembayaran) {
            $q->where('transaksis.metode_pembayaran', $metodePembayaran);
         })
         ->selectRaw('
            SUM(detail_transaksis.jumlah) AS total_item,
            SUM(detail_transaksis.total_harga_produk) AS total_penjualan,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "online"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS pembayaran_online,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "offline"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS pembayaran_offline
         ')
         ->first();


      $dataRetur = DB::table('validasi')
         ->join('detail_transaksis', 'detail_transaksis.id', '=', 'validasi.detail_transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->join('transaksis', 'transaksis.id', '=', 'validasi.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->select(
            'validasi.*',
            'transaksis.id as transaksi_id',
            'transaksis.kode_transaksi as kode',
            'transaksis.kasir_id as kasir_id',
            'produk_varian.nama as nama_varian',
         )
         ->orderByDesc('transaksis.created_at')
         ->get();

      // dd($dataRetur);


      // =========================
      // 5️⃣ RETURN VIEW
      // =========================
      return view('pages.kasir.histori_transaksi.index', [
         'title'     => 'Histori Transaksi',
         'transaksi' => $getTransaksi,
         'produkTerlaris' => $produkTerlaris,
         'rekap' => $rekapTransaksi,
         'tenant'    => $tenant,
         'shift'     => $shiftAktif,
         'getShifts' => $getShifts,
         'dataRetur' => $dataRetur
      ]);
   }

   public function exportHistoriTransaksi()
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      // $getShifts = $this->getShifts($tenantId);

      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      // =========================
      // WAKTU & SHIFT AKTIF
      // =========================
      $now        = Carbon::now();
      $timeNow   = $now->format('H:i:s');
      $today     = $now->toDateString();
      $yesterday = $now->copy()->subDay()->toDateString();

      $shiftAktif = Shift::where('tenant_id', $tenant->id)
         ->where(function ($q) use ($timeNow) {
            $q->where(function ($qq) use ($timeNow) {
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->where('start_time', '<=', $timeNow)
                  ->where('end_time', '>=', $timeNow);
            })
               ->orWhere(function ($qq) use ($timeNow) {
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qx) use ($timeNow) {
                        $qx->where('start_time', '<=', $timeNow)
                           ->orWhere('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();

      if (! $shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }

      // =========================
      // 4️⃣ QUERY TRANSAKSI (DINAMIS)
      // =========================
      [$startShift, $endShift, $tanggalShift, $namaShift] =
         $this->getRange($shiftAktif->id, $tenant->id);

      $transaksi = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->select(
            'transaksis.id as id_transaksi',
            'transaksis.*',
            'users.name as nama_kasir'
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->orderByDesc('transaksis.id')
         ->get();

      // dd($getTransaksi);


      // =========================
      // 6️⃣ PRODUK TERLARIS (SHIFT AKTIF)
      // =========================
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->select(
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            DB::raw('SUM(detail_transaksis.jumlah) AS total_terjual'),
            DB::raw('SUM(detail_transaksis.total_harga_produk) AS total_penjualan')
         )
         ->groupBy('produk.nama_produk', 'produk_varian.nama')
         ->orderByDesc('total_terjual')
         ->get();


      // =========================
      // 7️⃣ REKAP TRANSAKSI (SHIFT AKTIF)
      // =========================
      $rekap = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->selectRaw('
            SUM(detail_transaksis.jumlah) AS total_item,
            SUM(detail_transaksis.total_harga_produk) AS total_penjualan,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "online"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS pembayaran_online,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "offline"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS pembayaran_offline
         ')
         ->first();


      $dataRetur = DB::table('validasi_retur')
         ->join('detail_transaksis', 'detail_transaksis.id', '=', 'validasi_retur.detail_transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->join('transaksis', 'transaksis.id', '=', 'validasi_retur.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startShift, $endShift])
         ->select(
            'validasi_retur.id',
            'transaksis.id as transaksi_id',
            'transaksis.kode_transaksi as kode',
            'produk_varian.nama as nama_varian',
            'detail_transaksis.harga_satuan as harga_produk',
            'validasi_retur.jumlah_retur',
            DB::raw('(detail_transaksis.harga_satuan * validasi_retur.jumlah_retur) AS total_harga_retur'),
            'validasi_retur.status_validasi',
            'validasi_retur.keterangan'
         )
         ->orderByDesc('transaksis.created_at')
         ->get();

      // dd($dataRetur);

      // =========================
      // BUAT EXCEL
      // =========================
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $row = 1;

      $tanggalCetak = $this->tanggalShiftAktif($shiftAktif);
      $judul = 'DATA TRANSAKSI ' .
         strtoupper($tenant->name) . ' ' .
         strtoupper($shiftAktif->shift) . ' ' .
         $tanggalCetak;
      $sheet->setCellValue("A{$row}", $judul);
      $sheet->mergeCells("A{$row}:G{$row}");
      $sheet->getStyle("A{$row}")->getFont()->setBold(true);
      $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $row += 2;

      // HEADER
      $headers = ['Tanggal', 'Kode', 'Kasir', 'Pelanggan', 'Item', 'Pembayaran', 'Total'];
      $col = 'A';
      foreach ($headers as $header) {
         $sheet->setCellValue($col . $row, $header);
         $sheet->getStyle($col . $row)->getFont()->setBold(true);
         $col++;
      }
      $row++;

      $mapMetode = [
         'offline' => 'Tunai',
         'online'  => 'QRIS',
      ];

      foreach ($transaksi as $t) {

         $metodePembayaran = $mapMetode[$t->metode_pembayaran]
            ?? ucfirst($t->metode_pembayaran); // fallback jika ada value lain
         $sheet->fromArray([
            $t->created_at->format('d-m-Y H:i'),
            $t->kode_transaksi,
            $t->kasir->name,
            $t->nama_pelanggan,
            $t->total_item,
            $metodePembayaran,
            'Rp' . number_format($t->total_harga, 0, ',', '.')
         ], null, "A{$row}");
         $row++;
      }

      $sheet->getStyle("A3:G" . ($row - 1))
         ->getBorders()->getAllBorders()
         ->setBorderStyle(Border::BORDER_THIN);

      // =========================
      // REKAP
      // =========================
      $row += 1;
      $sheet->setCellValue("A{$row}", "REKAP TRANSAKSI");
      $sheet->mergeCells("A{$row}:G{$row}");
      $sheet->getStyle("A{$row}")->getFont()->setBold(true);
      $row++;

      $sheet->fromArray([
         ['Total Item', $rekap->total_item . ' item'],
         ['Total Penjualan', 'Rp' . number_format($rekap->total_penjualan ?? 0, 0, ',', '.')],
         ['Pembayaran Tunai', 'Rp' . number_format($rekap->offline ?? 0, 0, ',', '.')],
         ['Pembayaran QRIS', 'Rp' . number_format($rekap->online ?? 0, 0, ',', '.')],
      ], null, "A{$row}");

      // =========================
      // PRODUK TERLARIS
      // =========================
      $row += 6;
      $sheet->setCellValue("A{$row}", "PRODUK TERLARIS");
      $sheet->mergeCells("A{$row}:C{$row}");
      $sheet->getStyle("A{$row}")->getFont()->setBold(true);
      $row++;

      $sheet->fromArray(['Nama Produk', 'Varian', 'Total Terjual', 'Total Penjualan'], null, "A{$row}");
      $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
      $row++;

      foreach ($produkTerlaris as $p) {
         $sheet->fromArray([
            $p->nama_produk,
            $p->nama_varian,
            $p->total_terjual,
            'Rp' . number_format($p->total_penjualan, 0, ',', '.')
         ], null, "A{$row}");
         $row++;
      }

      // BORDER PRODUK
      $sheet->getStyle("A" . ($row - count($produkTerlaris) - 1) . ":D" . ($row - 1))
         ->getBorders()->getAllBorders()
         ->setBorderStyle(Border::BORDER_THIN);

      // AUTO SIZE
      $highestColumn = $sheet->getHighestColumn();
      $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

      for ($col = 1; $col <= $highestColumnIndex; $col++) {
         $sheet->getColumnDimension(
            Coordinate::stringFromColumnIndex($col)
         )->setAutoSize(true);
      }

      $filename =
         'DATA TRANSAKSI-' .
         strtoupper($tenant->name) . '-' .
         strtoupper($shiftAktif->shift) . '-' .
         str_replace('-', '', $tanggalCetak) .
         '.xlsx';
      return response()->streamDownload(function () use ($spreadsheet) {
         (new Xlsx($spreadsheet))->save('php://output');
      }, $filename);
   }

   public function detailTransaksi($id)
   {
      $tenantId = session('active_tenant_id');

      $getShifts = $this->dropdownShifts($tenantId);

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      $transaksi = Transaksi::findOrFail($id);

      // dd($transaksi);

      $kasir = User::where('id', $transaksi->kasir_id)
         ->select('users.id', 'users.name')
         ->first();
      // dd($kasir);

      $detailTransaksi = DetailTransaksi::join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->select(
            'detail_transaksis.id as id_detail_transaksi',
            'detail_transaksis.jumlah',
            'detail_transaksis.harga_satuan',
            'detail_transaksis.total_harga_produk',
            'produk_toko.id as id_produk_toko',
            'produk_toko.produk_id',
            'produk.nama_produk',
            'produk_varian.id as id_varian',
            'produk_varian.nama as nama_varian'
         )
         ->where('transaksi_id', $transaksi->id)->get();
      // dd($detailTransaksi);

      $tanggal_transaksi = Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('d F Y H:i');

      $validasi_retur = Validasi::join('detail_transaksis', 'detail_transaksis.id', '=', 'validasi.detail_transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->select(
            'validasi.*',
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            'produk_varian.harga_jual'
         )->where('validasi.transaksi_id', $transaksi->id)
         ->get();

      $data = [
         'title' => 'Detail Transaksi : ' . $transaksi->kode_transaksi,
         'transaksi' => $transaksi,
         'tanggal_transaksi' => $tanggal_transaksi,
         'kasir' => $kasir,
         'detail_transaksi' => $detailTransaksi,
         'tenant' => $tenant,
         'validasi_retur' => $validasi_retur,
         'getShifts'      => $getShifts
      ];

      return view('pages.kasir.histori_transaksi.detail_transaksi', $data);
   }

   public function returProduk()
   {
      $tenantId = session('active_tenant_id');
      // dd($tenantId);

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $getShifts = $this->dropdownShifts($tenantId);

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      $now        = Carbon::now();
      $timeNow   = $now->format('H:i:s');
      $today     = $now->toDateString();

      $shiftAktif = Shift::where('tenant_id', $tenant->id)
         ->where(function ($q) use ($timeNow) {

            $q->where(function ($qq) use ($timeNow) {
               // SHIFT NORMAL
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->where('start_time', '<=', $timeNow)
                  ->where('end_time', '>=', $timeNow);
            })
               ->orWhere(function ($qq) use ($timeNow) {
                  // SHIFT MALAM
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qqq) use ($timeNow) {
                        $qqq->where('start_time', '<=', $timeNow)
                           ->orWhere('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();

      if (! $shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }

      if ($shiftAktif->start_time < $shiftAktif->end_time) {
         // =========================
         // SHIFT NORMAL
         // =========================
         $start = Carbon::today()->setTimeFromTimeString($shiftAktif->start_time);
         $end   = Carbon::today()->setTimeFromTimeString($shiftAktif->end_time);
      } else {
         // =========================
         // SHIFT MALAM (lintas hari)
         // =========================
         if ($now->format('H:i:s') >= $shiftAktif->start_time) {
            // sebelum tengah malam
            $start = Carbon::today()->setTimeFromTimeString($shiftAktif->start_time);
            $end   = Carbon::tomorrow()->setTimeFromTimeString($shiftAktif->end_time);
         } else {
            // setelah tengah malam
            $start = Carbon::yesterday()->setTimeFromTimeString($shiftAktif->start_time);
            $end   = Carbon::today()->setTimeFromTimeString($shiftAktif->end_time);
         }
      }

      [$startShift, $endShift, $tanggalShift, $namaShift] =
         $this->getRange($shiftAktif->id, $tenant->id);


      $batalDanRetur = Validasi::with([
         'detailTransaksi.produkVarian',
         'transaksi',
         'admin'
      ])
         ->whereHas('transaksi', function ($q) use ($tenant, $startShift, $endShift) {
            $q->where('tenant_id', $tenant->id)
               ->whereBetween('created_at', [$startShift, $endShift]);
         })
         ->whereIn('type', ['pembatalan', 'retur'])
         ->where('status_validasi', 'diterima')
         ->orderBy('updated_at', 'desc') // waktu approval admin
         ->get();

      $totalHargaBatalRetur = $batalDanRetur->sum(
         function ($item) {
            return $item->harga_item;
         }
      );

      // dd($stokTokoKeluar);

      $data = [
         'title' => 'Retur Produk',
         'batal_retur' => $batalDanRetur,
         'totalHarga'  => $totalHargaBatalRetur,
         'tenant' => $tenant,
         'shiftAktif' => $shiftAktif,
         'getShifts'  => $getShifts
      ];

      return view('pages.kasir.retur_produk', $data);
   }

   public function pelanggan()
   {
      $tenantId = session('active_tenant_id');
      // dd($tenantId);

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $getShifts = $this->dropdownShifts($tenantId);

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      $getPelanggan = Pelanggan::all();

      $data = [
         'title' => 'Penukaran Poin Pelanggan',
         'pelanggan' => $getPelanggan,
         'tenant' => $tenant,
         'getShifts' => $getShifts
      ];

      return view('pages.kasir.pelanggan.index', $data);
   }

   public function storePelanggan(Request $request)
   {
      $validated = $request->validate(
         [
            'nama' => 'required|string|max:50',
            'alamat' => 'nullable|string|max:50',
            'whatsapp' => 'required|numeric|unique:pelanggans,telepon'
         ],
         [
            'nama.required' => 'Nama pelanggan wajib diisi',
            'nama.max' => 'Nama pelanggan maksimal 50 karakter',
            'alamat.max' => 'Alamat pelanggan maksimal 50 karakter',
            'whatsapp.numeric' => 'Format nomor WA tidak valid',
            'whatsapp.unique' => 'Nomor WA sudah terdaftar sebagai pelanggan',
         ]
      );

      $save = Pelanggan::insert([
         'nama' => $validated['nama'],
         'alamat' => $validated['alamat'],
         'telepon' => $validated['whatsapp'],
         'poin' => 0,
         'created_at' => Carbon::now()
      ]);

      if ($save) {
         return back()->with('success', 'Data pelanggan berhasil disimpan');
      } else {
         return back()->with('error', 'Data pelanggan gagal disimpan');
      }
   }

   public function updatePelanggan(Request $request, $id)
   {
      $pelanggan = Pelanggan::findOrFail($id);
      $validated = $request->validate(
         [
            'nama' => 'required|string|max:50',
            'alamat' => 'nullable|string|max:50',
            'whatsapp' => [
               'required',
               'numeric',
               Rule::unique('pelanggans', 'telepon')
                  ->ignore($pelanggan->id)
            ]
         ],
         [
            'nama.required' => 'Nama pelanggan wajib diisi',
            'nama.max' => 'Nama pelanggan maksimal 50 karakter',
            'alamat.max' => 'Alamat pelanggan maksimal 50 karakter',
            'whatsapp.numeric' => 'Format nomor WA tidak valid',
            'whatsapp.unique' => 'Nomor WA sudah terdaftar sebagai pelanggan',
         ]
      );

      $update = $pelanggan->update([
         'nama' => $validated['nama'],
         'alamat' => $validated['alamat'],
         'telepon' => $validated['whatsapp'],
      ]);

      if ($update) {
         return back()->with('success', 'Data pelanggan berhasil diupdate');
      } else {
         return back()->with('error', 'Data pelanggan gagal diupdate');
      }
   }

   public function prosesPenukaranPoin(Request $request, $id)
   {
      $getPelanggan = Pelanggan::findOrFail($id);

      $validated = $request->validate(
         [
            'poin_ditukar' => 'required|numeric|min:1',
         ],
         [
            'poin_ditukar.required' => 'Poin yang ditukar wajib diisi',
            'poin_ditukar.numeric' => 'Poin yang ditukar harus berupa angka',
            'poin_ditukar.min' => 'Poin yang ditukar minimal 1',
         ]
      );
      $poin_ditukar = $validated['poin_ditukar'];

      if ($poin_ditukar >= $getPelanggan->poin) {
         return redirect()->back()->with('error', 'Poin penukaran melebihi poin yang dimiliki');
      }

      $sisa_poin = $getPelanggan->poin - $poin_ditukar;

      $getPelanggan->update([
         'poin' => $sisa_poin
      ]);

      return redirect()->back()->with('success', 'Poin berhasil ditukar');
   }

   public function pengeluaranToko()
   {
      $tenantId = session('active_tenant_id');

      $getShifts = $this->dropdownShifts($tenantId);

      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();
      // =========================
      // VALIDASI TENANT
      // =========================
      if (! $tenant || ! $tenant->isActive) {
         return redirect()
            ->route('kasir.dashboard')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // ===============================
      // 1️⃣ AMBIL SHIFT AKTIF
      // ===============================
      $now        = Carbon::now();
      $timeNow   = $now->format('H:i:s');
      $today     = $now->toDateString();

      $shiftAktif = Shift::where('tenant_id', $tenant->id)
         ->where(function ($q) use ($timeNow) {

            $q->where(function ($qq) use ($timeNow) {
               // SHIFT NORMAL
               $qq->whereColumn('start_time', '<', 'end_time')
                  ->where('start_time', '<=', $timeNow)
                  ->where('end_time', '>=', $timeNow);
            })
               ->orWhere(function ($qq) use ($timeNow) {
                  // SHIFT MALAM
                  $qq->whereColumn('start_time', '>', 'end_time')
                     ->where(function ($qqq) use ($timeNow) {
                        $qqq->where('start_time', '<=', $timeNow)
                           ->orWhere('end_time', '>=', $timeNow);
                     });
               });
         })
         ->first();

      if (! $shiftAktif) {
         return redirect()
            ->route('logout')
            ->with('error', 'Shift aktif tidak ditemukan. Silakan login kembali.');
      }

      if ($shiftAktif->start_time < $shiftAktif->end_time) {
         // =========================
         // SHIFT NORMAL
         // =========================
         $start = Carbon::today()->setTimeFromTimeString($shiftAktif->start_time);
         $end   = Carbon::today()->setTimeFromTimeString($shiftAktif->end_time);
      } else {
         // =========================
         // SHIFT MALAM (lintas hari)
         // =========================
         if ($now->format('H:i:s') >= $shiftAktif->start_time) {
            // sebelum tengah malam
            $start = Carbon::today()->setTimeFromTimeString($shiftAktif->start_time);
            $end   = Carbon::tomorrow()->setTimeFromTimeString($shiftAktif->end_time);
         } else {
            // setelah tengah malam
            $start = Carbon::yesterday()->setTimeFromTimeString($shiftAktif->start_time);
            $end   = Carbon::today()->setTimeFromTimeString($shiftAktif->end_time);
         }
      }

      if (!$shiftAktif) {
         return back()->with('error', 'Shift aktif tidak ditemukan');
      }
      // dd($shiftAktif);

      // ===============================
      // 2️⃣ TENTUKAN RANGE SHIFT
      // ===============================
      [$startShift, $endShift, $tanggalShift, $namaShift] =
         $this->getRange($shiftAktif->id, $tenant->id);


      // dd($startShift, $endShift);

      // =========================
      // BASE QUERY (BERDASARKAN SHIFT)
      // =========================
      $baseQuery = Pengeluaran::with('user')
         ->where('tenant_id', $tenant->id)
         ->whereBetween('created_at', [$startShift, $endShift]);
      // dd($baseQuery);

      // =========================
      // DATA LIST
      // =========================
      $pengeluaran = (clone $baseQuery)
         ->orderBy('created_at', 'desc')
         ->get();

      // dd($pengeluaran);

      // =========================
      // TOTAL PENGELUARAN
      // =========================
      $total_pengeluaran = (clone $baseQuery)->sum('jumlah');

      // =========================
      // PENGELUARAN KASIR
      // =========================
      $pengeluaran_kasir = (clone $baseQuery)
         ->whereHas('user', function ($q) {
            $q->where('role', 'kasir');
         })
         ->sum('jumlah');

      // =========================
      // PENGELUARAN ADMIN
      // =========================
      $pengeluaran_admin = (clone $baseQuery)
         ->whereHas('user', function ($q) {
            $q->where('role', 'admin');
         })
         ->sum('jumlah');

      $title = $tenant->name . ' | Pengeluaran (Shift Aktif)';

      return view('pages.kasir.pengeluaran.index', [
         'pengeluaran'        => $pengeluaran,
         'tenant'             => $tenant,
         'title'              => $title,
         'pengeluaran_kasir'  => $pengeluaran_kasir,
         'pengeluaran_admin'  => $pengeluaran_admin,
         'total_pengeluaran'  => $total_pengeluaran,
         'shift_start'        => $startShift,
         'shift_end'          => $endShift,
         'shiftAktif'         => $shiftAktif,
         'getShifts'          => $getShifts
      ]);
   }

   public function addPengeluaranToko()
   {
      $tenantId = session('active_tenant_id');

      $getShifts = $this->dropdownShifts($tenantId);

      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();
      // =========================
      // VALIDASI TENANT
      // =========================
      if (! $tenant || ! $tenant->isActive) {
         return redirect()
            ->route('kasir.dashboard')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      $title = 'Tambah Pengeluaran Toko';

      return view('pages.kasir.pengeluaran.add', compact('tenant', 'title', 'getShifts'));
   }

   public function storePengeluaranToko(Request $request)
   {
      // dd($request->all(), $id);
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $getShifts = $this->dropdownShifts($tenantId);

      $tenant = Tenant::findOrFail($tenantId);

      if (! $tenant) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
      }

      $messages = [
         'deskripsi.required' => 'Deskripsi pengeluaran wajib diisi.',
         'deskripsi.max' => 'Deskripsi maksimal 100 karakter.',
         'jumlah.required' => 'Jumlah pengeluaran wajib diisi.',
         'jumlah.numeric' => 'Jumlah pengeluaran harus berupa angka.',
      ];

      $validate = $request->validate([
         'deskripsi' => 'required|string|max:100',
         'jumlah' => 'required|numeric',
      ], $messages);

      $jumlah = str_replace('.', '', $validate['jumlah']);

      $create = $tenant->pengeluaran()->create([
         'tenant_id' => $tenant->id,
         'deskripsi' => $validate['deskripsi'],
         'jumlah' => $jumlah,
         'user_id' => Auth::user()->id,
         'tanggal' => Carbon::now()->toDateString(),
         'created_at' => Carbon::now()
      ]);

      if (! $create) {
         return redirect()->back()->with('error', 'Gagal menambahkan pengeluaran.');
      }

      return redirect()->route('kasir.pengeluaranToko', compact('getShifts'))->with('success', 'Pengeluaran berhasil ditambahkan.');
   }

   public function editPengeluaranToko($id)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $getShifts = $this->dropdownShifts($tenantId);

      $tenant = Tenant::findOrFail($tenantId);
      $pengeluaran = $tenant->pengeluaran()->where('id', $id)->firstOrFail();
      $title = 'Edit Pengeluaran Toko';
      return view('pages.kasir.pengeluaran.edit', compact('pengeluaran', 'tenant', 'title', 'getShifts'));
   }

   public function updatePengeluaranToko(Request $request, $id)
   {
      $tenantId = session('active_tenant_id');
      // dd($request->all(), $id, $tenantId);

      if (!$tenantId) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
      }

      // $getShifts = $this->getShifts($tenantId);

      $tenant = Tenant::findOrFail($tenantId);

      if (! $tenant) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
      }

      $messages = [
         'deskripsi.required' => 'Deskripsi pengeluaran wajib diisi.',
         'deskripsi.max' => 'Deskripsi maksimal 100 karakter.',
         'jumlah.required' => 'Jumlah pengeluaran wajib diisi.',
         'jumlah.numeric' => 'Jumlah pengeluaran harus berupa angka.',
      ];

      $validate = $request->validate([
         'deskripsi' => 'required|string|max:100',
         'jumlah' => 'required|numeric',
      ], $messages);

      $jumlah = str_replace('.', '', $validate['jumlah']);

      $pengeluaran = $tenant->pengeluaran()->where('id', $id)->firstOrFail();

      $update = $pengeluaran->update([
         'deskripsi' => $validate['deskripsi'],
         'jumlah' => $jumlah,
         'updated_at' => Carbon::now()
      ]);

      if (! $update) {
         return redirect()->back()->with('error', 'Gagal memperbarui pengeluaran.');
      }

      return redirect()->route('kasir.pengeluaranToko', $tenant->id)->with('success', 'Pengeluaran berhasil diperbarui.');
   }

   // histori stok masuk
   public function historiStokMasuk()
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $getShifts = $this->dropdownShifts($tenantId);

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant tidak ditemukan atau tidak aktif.']);
      }

      $stok = Stok::with(['produk', 'user'])
         ->where('tenant_id', $tenant->id)
         ->whereDate('created_at', Carbon::today())
         ->latest()
         ->get();;

      // dd($stok);
      $title = 'Histori Stok Masuk';

      return view('pages.kasir.stok-masuk.index', compact('stok', 'title', 'tenant', 'getShifts'));
   }

   // catatan
   public function catatan()
   {
      $title = 'Catatan';
      $user = auth()->user();

      $tenantId = session('active_tenant_id');
      $getShifts = $this->dropdownShifts($tenantId);

      // Ambil tenant yang bisa diakses oleh kasir
      $tenantIds = $user->tenants()->pluck('tenants.id');

      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantIds)
         ->where('isActive', true)
         ->first();

      // Ambil catatan berdasarkan tenant yang diizinkan
      $catatan = Catatan::with(['kasir', 'admin'])
         ->whereIn('tenant_id', $tenantIds)
         ->latest()
         ->get();

      // dd($tenantIds, $catatan, $tenant);
      return view('pages.kasir.catatan.index', compact('catatan', 'title', 'tenant', 'getShifts'));
   }

   public function catatanStore(Request $request)
   {
      $user = auth()->user();

      // Ambil tenant yang bisa diakses oleh kasir
      $tenantIds = $user->tenants()->pluck('tenants.id');
      // 1️⃣ Ambil tenant aktif
      $tenant = Tenant::where('id', $tenantIds)
         ->where('isActive', true)
         ->first();

      $validated = $request->validate(
         [
            'catatan' => 'required|string',
            'admin_id' => 'prohibited'
         ],
         [
            'catatan.required' => 'Catatan harus diisi.'
         ]
      );

      $save = Catatan::insert([
         'tenant_id' => $tenant->id,
         'catatan' => $validated['catatan'],
         'kasir_id' => $user->id,
         'created_at' => Carbon::now(),
      ]);

      if ($save) {
         return back()->with('success', 'Catatan berhasil ditambahkan');
      } else {
         return back()->with('error', 'Catatan gagal ditambahkan');
      }
      // dd($tenantIds, $validated);
   }

   public function catatanUpdate(Request $request, $id)
   {
      $catatan = Catatan::findOrFail($id);
      $validated = $request->validate(
         [
            'catatan' => 'required|string',
            'admin_id' => 'prohibited'
         ],
         [
            'catatan.required' => 'Catatan harus diisi.'
         ]
      );

      $update = 0;

      if ($catatan->admin_id == null) {
         $update = $catatan->update([
            'catatan' => $validated['catatan'],
            'updated_at' => Carbon::now(),
         ]);
      }

      if ($update) {
         return back()->with('success', 'Catatan berhasil diupdate.');
      } else {
         return back()->with('error', 'Catatan gagal diupdate.');
      }
   }

   public function cetakRekap($shiftId)
   {
      $tenantId = session('active_tenant_id');
      $shiftId  = $shiftId;
      $tenant = Tenant::where('id', $tenantId)->first();
      // dd($tenant);
      // dd($tenantId, $shiftId);

      [$start, $end, $tanggalShift, $nama_shift] = $this->getRange($shiftId, $tenantId);

      $penjualan = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->selectRaw('
         SUM(detail_transaksis.jumlah) as total_item,
         SUM(detail_transaksis.jumlah * detail_transaksis.harga_satuan) as total_harga
         ')->first();

      $penjualanPerMetode = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->selectRaw('
         transaksis.metode_pembayaran,
         SUM(detail_transaksis.jumlah * detail_transaksis.harga_satuan) as total_harga
         ')
         ->groupBy('transaksis.metode_pembayaran')
         ->get();

      $totalTunai = 0;
      $totalQris  = 0;

      foreach ($penjualanPerMetode as $item) {
         if ($item->metode_pembayaran === 'offline') {
            $totalTunai = $item->total_harga;
         }

         if ($item->metode_pembayaran === 'online') {
            $totalQris = $item->total_harga;
         }
      }

      $retur = DB::table('stok_toko_keluar')
         ->where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$start, $end])
         ->selectRaw('
         SUM(jumlah_retur) as total_item_retur,
         SUM(jumlah_retur * harga_produk) as total_harga_retur
         ')
         ->first();

      $pengeluaran = DB::table('pengeluaran')
         ->where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$start, $end])
         ->sum('jumlah');

      $totalPenjualan = $penjualan->total_harga ?? 0;
      $totalRetur     = $retur->total_harga_retur ?? 0;
      $totalKeluar    = $pengeluaran ?? 0;

      $labaTunai = $totalTunai - $pengeluaran;

      $labaBersih = $totalPenjualan - $totalRetur - $totalKeluar;

      // dd($totalPenjualan, $totalRetur, $totalKeluar, $labaBersih);

      return view('pages.kasir.cetak-rekap', compact(
         'tenant',
         'start',
         'end',
         'tanggalShift',
         'nama_shift',
         'penjualan',
         'retur',
         'pengeluaran',
         'labaTunai',
         'labaBersih',
         'totalTunai',
         'totalQris'
      ))->render();
   }


   private function lr($left, $right, $width = 32)
   {
      $left  = trim((string) $left);
      $right = trim((string) $right);

      // Potong kalau kepanjangan
      if (strlen($left) > $width) {
         $left = substr($left, 0, $width);
      }

      if (strlen($right) > $width) {
         $right = substr($right, 0, $width);
      }

      $space = $width - strlen($left) - strlen($right);
      if ($space < 1) $space = 1;

      return $left . str_repeat(' ', $space) . $right . "\n";
   }

   private function center($text, $width = 32)
   {
      $text = trim((string) $text);

      if (strlen($text) >= $width) {
         return substr($text, 0, $width) . "\n";
      }

      $leftPadding = floor(($width - strlen($text)) / 2);

      return str_repeat(' ', $leftPadding) . $text . "\n";
   }


   public function cetakRekapRawBT($shiftId)
   {
      $tenantId = session('active_tenant_id');
      $shiftId  = $shiftId;
      $tenant   = Tenant::findOrFail($tenantId);

      [$start, $end, $tanggalShift, $nama_shift] = $this->getRange($shiftId, $tenantId);

      // === DATA (SAMA SEPERTI PUNYAMU) ===
      // [query penjualan, retur, pengeluaran TIDAK DIUBAH]
      $penjualan = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->selectRaw('
      SUM(detail_transaksis.jumlah) as total_item,
      SUM(detail_transaksis.jumlah * detail_transaksis.harga_satuan) as total_harga
      ')->first();

      $penjualanPerMetode = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenantId)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->selectRaw('
      transaksis.metode_pembayaran,
      SUM(detail_transaksis.jumlah * detail_transaksis.harga_satuan) as total_harga
      ')
         ->groupBy('transaksis.metode_pembayaran')
         ->get();

      $totalTunai = 0;
      $totalQris  = 0;

      foreach ($penjualanPerMetode as $item) {
         if ($item->metode_pembayaran === 'offline') {
            $totalTunai = $item->total_harga;
         }

         if ($item->metode_pembayaran === 'online') {
            $totalQris = $item->total_harga;
         }
      }

      $retur = DB::table('stok_toko_keluar')
         ->where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$start, $end])
         ->selectRaw('
         SUM(jumlah_retur) as total_item_retur,
         SUM(jumlah_retur * harga_produk) as total_harga_retur
         ')
         ->first();

      $pengeluaran = DB::table('pengeluaran')
         ->where('tenant_id', $tenantId)
         ->whereBetween('created_at', [$start, $end])
         ->sum('jumlah');

      $totalPenjualan = $penjualan->total_harga ?? 0;
      $totalRetur     = $retur->total_harga_retur ?? 0;
      $totalKeluar    = $pengeluaran ?? 0;

      $labaTunai = $totalTunai - $pengeluaran;

      $labaBersih = $totalPenjualan - $totalRetur - $totalKeluar;

      // === TEXT STRUK (ASCII ONLY) ===
      $struk = "\n";
      $struk .= $this->center(strtoupper($tenant->name));
      $struk .= $this->center(" REKAP SHIFT");
      $struk .= str_repeat('-', 32) . "\n";

      $struk .= "Tanggal : {$tanggalShift}\n";
      $struk .= $this->lr('Shift   : ' . $nama_shift, $start->format("H:i") . ' - ' . $end->format("H:i"));

      $struk .= str_repeat('-', 32) . "\n";

      $struk .= $this->lr('Item Terjual', $penjualan->total_item ?? 0 . ' item');
      $struk .= $this->lr('Tunai', 'Rp' . number_format($totalTunai, 0, ',', '.'));
      $struk .= $this->lr('QRIS', 'Rp' . number_format($totalQris, 0, ',', '.'));
      $struk .= $this->lr('Total Jual', 'Rp' . number_format($totalPenjualan, 0, ',', '.'));

      $struk .= str_repeat('-', 32) . "\n";

      $struk .= $this->lr('Pengeluaran', 'Rp' . number_format($pengeluaran, 0, ',', '.'));
      $struk .= $this->lr('Laba Tunai', 'Rp' . number_format($labaTunai, 0, ',', '.'));

      $struk .= str_repeat('-', 32) . "\n";

      $struk .= $this->lr('Barang Keluar', $retur->total_item_retur ?? 0 . ' item');
      $struk .= $this->lr('Harga Keluar', 'Rp' . number_format($retur->total_harga_retur, 0, ',', '.'));

      $struk .= str_repeat('-', 32) . "\n";

      $struk .= $this->lr('LABA BERSIH', 'Rp' . number_format($labaBersih, 0, ',', '.'));

      $struk .= str_repeat('-', 32) . "\n";

      $struk .= $this->center("Kasir : " . auth()->user()->name);
      $struk .= $this->center(now()->format('d/m/Y H:i'));

      return response($struk)
         ->header('Content-Type', 'text/plain; charset=ASCII');
   }
}
