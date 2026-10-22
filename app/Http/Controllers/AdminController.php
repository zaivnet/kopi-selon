<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Stok;
use App\Models\User;
use App\Models\Shift;
use App\Models\Gudang;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\Catatan;
use App\Models\Printer;
use App\Models\Kategori;
use App\Models\Validasi;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\ProdukToko;
use App\Models\Pengeluaran;
use Illuminate\Support\Str;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use App\Models\ValidasiRetur;
use App\Models\StokTokoKeluar;
use App\Models\DetailTransaksi;
use App\Models\StokGudangMasuk;
use Illuminate\Validation\Rule;
use App\Models\StokGudangKeluar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
   /**
    * Display a listing of the resource.
    */

   public function index()
   {
      $user = auth()->user();
      if (! $user) {
         return redirect()->route('login')->with('error', 'User tidak ditemukan.');
      }
      // ambil data tenant yg aktif dan terkait dengan user
      $tenant = $user->tenants()->where('isActive', true)->get();
      $title = 'Toko Saya';
      return view('pages.admin.index', compact('tenant', 'title'));
   }

   public function setTenant($tenantId)
   {
      $user = auth()->user();

      // Ambil tenant
      $tenant = Tenant::findOrFail($tenantId);

      if (! $tenant->isActive) {
         return back()->with('error', 'Toko tidak aktif.');
      }

      // Cek akses (kecuali superadmin)
      if ($user->role !== 'superadmin') {
         if (! $user->tenants()->where('tenant_id', $tenantId)->exists()) {
            return back()->with('error', 'Anda tidak memiliki akses ke toko ini.');
         }
      }

      // Simpan tenant aktif ke session
      session(['active_tenant_id' => $tenantId]);

      return redirect()->route('admin.dashboard');
   }

   public function dashboard(Request $request)
   {
      $tenant = $request->tenant;

      $getGudang = DB::table('gudang_has_tenants')
         ->join('gudang', 'gudang.id', '=', 'gudang_has_tenants.gudang_id')
         ->where('gudang_has_tenants.tenant_id', $tenant->id)
         ->select('gudang.*')
         ->first(); // atau ->get() jika bisa lebih dari 1

      if (!$getGudang) {
         return redirect()->route('admin.index')->with('error', 'Toko ini belum memiliki gudang');
      }

      // ==========================
      // INPUT FILTER
      // ==========================
      $now = Carbon::now();

      // ⏰ sebelum jam 6 → masih hari kerja kemarin
      $selectedDate = $request->date
         ?? ($now->hour < 6
            ? $now->copy()->subDay()->toDateString()
            : $now->toDateString());
      $selectedShiftId = $request->shift_id;

      // ==========================
      // AMBIL SHIFT PERTAMA (PATOKAN HARI OPERASIONAL)
      // ==========================
      $shiftPertama = Shift::where('tenant_id', $tenant->id)
         ->orderBy('start_time')
         ->first();

      $getShifts = Shift::where('tenant_id', $tenant->id)->exists();

      if (!$getShifts) {
         return redirect()->route('admin.shift')->with('error', 'Jam shift belum ada, silahkan buat shift dulu.');
      }

      // ==========================
      // TENTUKAN RANGE WAKTU (KUNCI UTAMA)
      // ==========================
      $hari = Carbon::parse($selectedDate);

      // hari operasional
      $start = $hari->copy()->setTime(6, 0, 0);
      $end   = $start->copy()->addDay()->subSecond();

      $shiftAktif = null;
      if ($selectedShiftId) {
         $shiftAktif = Shift::where('tenant_id', $tenant->id)
            ->where('id', $selectedShiftId)
            ->first();
      }

      // ⚠️ hanya override range kalau shift dipilih
      if ($shiftAktif) {

         $start = Carbon::parse($selectedDate . ' ' . $shiftAktif->start_time);

         // shift mulai sebelum jam reset → masuk hari kerja berikutnya
         if (Carbon::parse($shiftAktif->start_time)->hour < 6) {
            $start->addDay();
         }

         // shift lintas hari
         if ($shiftAktif->start_time > $shiftAktif->end_time) {
            $end = Carbon::parse($selectedDate . ' ' . $shiftAktif->end_time)->addDay();
         } else {
            $end = Carbon::parse($selectedDate . ' ' . $shiftAktif->end_time);
         }
      }


      // ==========================
      // BASE QUERY TRANSAKSI
      // ==========================
      $transaksiFilter = Transaksi::where('tenant_id', $tenant->id)
         ->whereBetween('created_at', [$start, $end]);

      // ==========================
      // BASE QUERY STOK KELUAR (RETUR)
      // ==========================
      $stokKeluarFilter = DB::table('stok_toko_keluar')
         ->where('tenant_id', $tenant->id)
         ->whereBetween('created_at', [$start, $end]);

      // ==========================
      // TOTAL PRODUK ETALASE
      // ==========================
      $totalProdukEtalase = ProdukToko::where('tenant_id', $tenant->id)->count();

      // ==========================
      // TOTAL PRODUK TERJUAL
      // ==========================
      $totalProdukTerjual = DetailTransaksi::join(
         'transaksis',
         'transaksis.id',
         '=',
         'detail_transaksis.transaksi_id'
      )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->sum('detail_transaksis.jumlah');

      // ==========================
      // TOTAL PENDAPATAN
      // ==========================
      $pendapatan = (clone $transaksiFilter)->sum('total_harga');

      $tunai = (clone $transaksiFilter)->where('metode_pembayaran', 'offline')->sum('total_harga');

      $qris = (clone $transaksiFilter)->where('metode_pembayaran', 'online')->sum('total_harga');

      // ==========================
      // TOTAL RETUR (QTY)
      // ==========================
      $totalProdukRetur = (clone $stokKeluarFilter)->sum('jumlah_retur');

      // ==========================
      // TOTAL HARGA RETUR
      // ==========================
      $totalHargaRetur = (clone $stokKeluarFilter)
         ->sum(DB::raw('jumlah_retur * harga_produk'));

      // ==========================
      // TOTAL PENGELUARAN
      // ==========================
      $pengeluaran = Pengeluaran::where('tenant_id', $tenant->id)
         ->whereBetween('created_at', [$start, $end])
         ->sum('jumlah');

      $totalPengeluaran = $pengeluaran + $totalHargaRetur;

      // ==========================
      // LABA BERSIH
      // ==========================
      $laba = $pendapatan - $totalPengeluaran;

      // ==========================
      // PRODUK TERLARIS
      // ==========================
      $produkTerlaris = DetailTransaksi::join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$start, $end])
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

      // ==========================
      // AKTIVITAS TRANSAKSI
      // ==========================
      $aktivitasTransaksi = DB::table('transaksis')
         ->join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->join('detail_transaksis', 'detail_transaksis.transaksi_id', '=', 'transaksis.id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->select(
            'transaksis.id',
            'transaksis.kode_transaksi',
            'transaksis.nama_pelanggan',
            'transaksis.total_harga',
            'transaksis.created_at',
            'users.name as nama_kasir',
            DB::raw('SUM(detail_transaksis.jumlah) AS total_item')
         )
         ->groupBy(
            'transaksis.id',
            'transaksis.kode_transaksi',
            'transaksis.nama_pelanggan',
            'transaksis.kasir_id',
            'transaksis.total_harga',
            'transaksis.created_at',
            'users.name'
         )
         ->orderByDesc('transaksis.created_at')
         ->limit(10)
         ->get();

      // ==========================
      // DATA SHIFT (DROPDOWN)
      // ==========================
      $shifts = Shift::where('tenant_id', $tenant->id)->get();

      // ==========================
      // DATA SHIFT (DROPDOWN)
      // ==========================
      $shifts = Shift::where('tenant_id', $tenant->id)->get();

      $bulanIni = Carbon::parse($selectedDate)->month;
      $tahunIni = Carbon::parse($selectedDate)->year;

      $pendapatanBulanan = DB::table('transaksis')
         ->where('tenant_id', $tenant->id)
         ->whereMonth('created_at', $bulanIni)
         ->whereYear('created_at', $tahunIni)
         ->select(
            DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) as tanggal'),
            DB::raw('SUM(total_harga) as total')
         )
         ->groupBy(DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))'))
         ->pluck('total', 'tanggal');

      $retur = DB::table('stok_toko_keluar')
         ->where('tenant_id', $tenant->id)
         ->whereMonth('created_at', $bulanIni)
         ->whereYear('created_at', $tahunIni)
         ->select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('SUM(jumlah_retur * harga_produk) as total')
         )
         ->groupBy(DB::raw('DATE(created_at)'))
         ->pluck('total', 'tanggal');

      $pengeluaranBulanan = DB::table('pengeluaran')
         ->where('tenant_id', $tenant->id)
         ->whereMonth('created_at', $bulanIni)
         ->whereYear('created_at', $tahunIni)
         ->select(
            DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) as tanggal'),
            DB::raw('SUM(jumlah) as total')
         )
         ->groupBy(DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))'))
         ->pluck('total', 'tanggal');


      $labels = [];
      $seriesPendapatan = [];
      $seriesPengeluaran = [];

      $start = Carbon::now()->startOfMonth();
      $end   = Carbon::now()->endOfMonth();

      for ($date = $start; $date <= $end; $date->addDay()) {

         $tgl = $date->toDateString();

         $labels[] = $date->translatedFormat('d');

         $seriesPendapatan[] = (int) ($pendapatanBulanan[$tgl] ?? 0);

         $totalRetur = (int) ($retur[$tgl] ?? 0);
         $totalPengeluaran = (int) ($pengeluaranBulanan[$tgl] ?? 0);

         $seriesPengeluaran[] = $totalRetur + $totalPengeluaran;
      }

      $title = $tenant->name . ' | Dashboard';

      return view('pages.admin.dashboard', compact(
         'tenant',
         'title',
         'totalProdukEtalase',
         'pendapatan',
         'totalProdukTerjual',
         'totalProdukRetur',
         'totalHargaRetur',
         'laba',
         'pengeluaran',
         'totalPengeluaran',
         'shifts',
         'selectedDate',
         'selectedShiftId',
         'produkTerlaris',
         'aktivitasTransaksi',
         'pendapatanBulanan',
         'labels',
         'seriesPendapatan',
         'seriesPengeluaran',
         'tunai',
         'qris'
      ));
   }

   public function kategori(Request $request)
   {

      $tenant = $request->tenant;
      // cek status tenant dan batasi agar tidak dapat masuk ke tanant yg tidak aktif
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Toko tidak ditemukan atau tidak aktif.');
      }

      $user = auth()->user();

      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      $gudang = Gudang::findOrFail($gudangId);
      $kategori = $gudang->kategori;
      $title = $tenant->name . ' | Kategori';
      return view('pages.admin.kategori.index', compact('tenant', 'kategori', 'title'));
   }

   public function storeKategori(Request $request)
   {
      // 1️⃣ Ambil tenant aktif
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang dari tenant (via pivot)
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Tenant tidak memiliki akses gudang.');
      }
      $user = auth()->user();
      $hasAccess = DB::table('user_has_tenants')
         ->where('user_id', $user->id)
         ->where('tenant_id', $tenant->id)
         ->exists();

      if (! $hasAccess) {
         return back()->with('error', 'Anda tidak memiliki akses ke tenant ini.');
      }

      // 3️⃣ Validasi input
      $messages = [
         'kategori.required' => 'Kategori wajib diisi.',
         'kategori.string'   => 'Kategori harus berupa teks.',
         'kategori.max'      => 'Kategori maksimal 30 karakter.',
         'kategori.unique'   => 'Kategori sudah ada di gudang ini.',
      ];

      $validated = $request->validate([
         'kategori' => [
            'required',
            'string',
            'max:30',

            // 🔥 UNIQUE PER GUDANG
            Rule::unique('kategori', 'nama_kategori')
               ->where(function ($q) use ($gudangId) {
                  $q->whereIn('id', function ($sub) use ($gudangId) {
                     $sub->select('kategori_id')
                        ->from('gudang_kategori')
                        ->where('gudang_id', $gudangId);
                  });
               }),
         ],
      ], $messages);

      DB::transaction(function () use ($validated, $gudangId) {

         // 4️⃣ Simpan / ambil kategori (GLOBAL)
         $kategori = Kategori::firstOrCreate([
            'nama_kategori' => $validated['kategori'],
         ]);

         // 5️⃣ Attach ke gudang (jika belum ada)
         DB::table('gudang_kategori')->updateOrInsert(
            [
               'gudang_id'   => $gudangId,
               'kategori_id' => $kategori->id,
            ],
            [
               'created_at' => now(),
               'updated_at' => now(),
            ]
         );
      });

      return redirect()
         ->route('admin.kategori')
         ->with('success', 'Kategori berhasil ditambahkan.');
   }

   public function updateKategori(Request $request, $id)
   {
      // 1️⃣ Ambil tenant aktif
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang dari tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Tenant tidak memiliki akses gudang.');
      }

      $user = auth()->user();
      $hasAccess = DB::table('user_has_tenants')
         ->where('user_id', $user->id)
         ->where('tenant_id', $tenant->id)
         ->exists();

      if (! $hasAccess) {
         return back()->with('error', 'Anda tidak memiliki akses ke tenant ini.');
      }

      // 3️⃣ Ambil kategori
      $kategori = Kategori::findOrFail($id);

      // 4️⃣ Validasi (UNIQUE PER GUDANG)
      $messages = [
         'kategori.required' => 'Kategori wajib diisi.',
         'kategori.string'   => 'Kategori harus berupa string.',
         'kategori.max'      => 'Kategori maksimal 30 karakter.',
         'kategori.unique'   => 'Kategori sudah ada di gudang ini.',
      ];

      $validated = $request->validate([
         'kategori' => [
            'required',
            'string',
            'max:30',

            Rule::unique('kategori', 'nama_kategori')
               ->ignore($kategori->id)
               ->where(function ($q) use ($gudangId) {
                  $q->whereIn('id', function ($sub) use ($gudangId) {
                     $sub->select('kategori_id')
                        ->from('gudang_kategori')
                        ->where('gudang_id', $gudangId);
                  });
               }),
         ],
      ], $messages);

      // 5️⃣ Update nama kategori (GLOBAL)
      $kategori->nama_kategori = $validated['kategori'];
      $kategori->save();

      return redirect()
         ->route('admin.kategori')
         ->with('success', 'Kategori berhasil diperbarui.');
   }

   public function deleteKategori(Request $request, $id)
   {
      $tenant = $request->tenant;
      $kategori = Kategori::findOrFail($id);
      if (! $kategori) {
         return redirect()->back()->with('error', 'Kategori tidak ditemukan.');
      }

      // =========================
      // 3️⃣ AMBIL GUDANG TENANT
      // =========================
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return redirect()->back()->with('error', 'Gudang tenant tidak ditemukan.');
      }

      // =========================
      // 4️⃣ CEK RELASI GUDANG ↔ KATEGORI
      // =========================
      $isValid = DB::table('gudang_kategori')
         ->where('gudang_id', $gudangId)
         ->where('kategori_id', $kategori->id)
         ->exists();

      if (! $isValid) {
         return redirect()->back()->with(
            'error',
            'Anda tidak memiliki akses untuk menghapus kategori ini.'
         );
      }


      $delete = $kategori->delete();

      if (! $delete) {
         return redirect()->back()->with('error', 'Gagal menghapus kategori.');
      }

      return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
   }

   public function produkGudang(Request $request)
   {
      $tenant = $request->tenant;
      // cek status tenant dan batasi agar tidak dapat masuk ke tanant yg tidak aktif
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // Ambil gudang yang bisa diakses tenant
      $gudang = $tenant->gudangs()->get();

      // ❌ Tenant tidak punya akses gudang
      if ($gudang->isEmpty()) {
         return redirect()->route('admin.index')
            ->with('error', 'Tenant ini tidak memiliki akses ke gudang manapun.');
      }

      $produk = Produk::whereHas('gudang.tenants', function ($q) use ($tenant) {
         $q->where('tenants.id', $tenant->id);
      })
         ->with([
            'tenant' => function ($q) use ($tenant) {
               $q->where('tenant_id', $tenant->id)
                  ->withPivot('display'); // ⬅️ HANYA display
            }
         ])
         ->get()
         ->map(function ($p) {

            $pivot = $p->tenant->first()?->pivot;

            // Properti khusus view produk gudang
            $p->is_displayed = (bool) ($pivot?->display ?? false);
            $p->has_pivot   = (bool) $pivot;

            return $p;
         });
      // dd($produk);
      $kategori = Kategori::all();

      $riwayat = StokGudangMasuk::whereHas('gudang.tenants', function ($q) use ($tenant) {
         $q->where('tenants.id', $tenant->id);
      })
         ->with(['produk', 'gudang', 'admin'])
         ->latest()
         ->get();

      $stok = StokGudangMasuk::first();

      // dd($gudang, $tenant, $riwayat, $stok->gudang);

      $title = $tenant->name . ' | Gudang';
      return view('pages.admin.gudang.index', compact('tenant', 'gudang', 'produk', 'kategori', 'title', 'riwayat'));
   }

   public function produkGudangAdd(Request $request)
   {
      $tenant = $request->tenant;

      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      $gudang = Gudang::findOrFail($gudangId);
      $kategori = $gudang->kategori;
      $title = 'Tambah Produk';
      return view('pages.admin.gudang.add', compact('tenant', 'title', 'kategori'));
   }

   public function storeProdukGudang(Request $request)
   {
      // 1️⃣ Pastikan tenant ada
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang milik tenant (dari pivot)
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Toko ini belum memiliki gudang.');
      }


      $messages = [
         'kode.max' => 'Kode barang maksimal 50 karakter.',
         'kode.unique' => 'Kode barang sudah digunakan produk lain.',
         'nama_produk.required' => 'Nama produk wajib diisi.',
         'nama_produk.max' => 'Nama produk maksimal 50 karakter.',
         'nama_produk.unique' => 'Nama produk sudah ada di gudang',
         'kategori.required' => 'Kategori wajib dipilih.',
         'kategori.exists' => 'Kategori tidak ditemukan.',
         'stok_awal.required' => 'Stok awal wajib diisi.',
         'stok_awal.integer' => 'Stok harus berupa angka.',
         'stok_awal.min' => 'Stok minimal 1.',
         'harga_beli.required' => 'Harga pembelian wajib diisi.',
         'harga_beli.integer' => 'Harga pembelian harus berupa angka.',
      ];

      $validated = $request->validate([
         'kode' => [
            'nullable',
            'string',
            'max:50',
            Rule::unique('produk', 'kode')
               ->where('gudang_id', $gudangId),
         ],
         'nama_produk' => [
            'required',
            'string',
            'max:50',
            Rule::unique('produk', 'nama_produk')
               ->where('gudang_id', $gudangId),
         ],
         'kategori' => 'required|exists:kategori,id',
         'stok_awal' => 'required|integer|min:1',
         'harga_beli' => 'required|numeric',
      ], $messages);

      // dd($validated);

      DB::transaction(function () use ($validated, $gudangId) {

         // 5️⃣ Simpan produk
         $produk = Produk::create([
            'gudang_id'   => $gudangId,
            'kode'        => $validated['kode'],
            'nama_produk' => $validated['nama_produk'],
            'kategori_id' => $validated['kategori'],
            'stok_gudang' => $validated['stok_awal'],
         ]);

         // 6️⃣ Simpan histori stok masuk
         StokGudangMasuk::create([
            'gudang_id'        => $gudangId,
            'produk_id'        => $produk->id,
            'user_id'          => auth()->id(),
            'qty_masuk'        => $validated['stok_awal'],
            'harga_pembelian' => str_replace('.', '', $validated['harga_beli']),
         ]);
      });

      return redirect()
         ->route('admin.produk.add')
         ->with('success', 'Produk berhasil ditambahkan.');
   }

   public function produkGudangEdit(Request $request, $produkId)
   {
      $tenant = $request->tenant;
      $produk = Produk::findOrFail($produkId);
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      $gudang = Gudang::findOrFail($gudangId);
      $kategori = $gudang->kategori;
      $title = 'Edit Produk';
      return view('pages.admin.gudang.edit', compact('tenant', 'title', 'produk', 'kategori'));
   }

   public function updateProdukGudang(Request $request, $produkId)
   {
      $tenant = $request->tenant;

      // Ambil gudang yang bisa diakses tenant
      // 2️⃣ Ambil gudang milik tenant (dari pivot)
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      // produk gudang
      $produk = Produk::with('gudang')->findOrFail($produkId);

      // 🔐 CEK: tenant punya akses ke gudang produk?
      $hasAccess = $produk->gudang
         ->tenants()
         ->where('tenants.id', $tenant->id)
         ->exists();

      if (! $hasAccess) {
         return back()->with('error', 'Anda tidak memiliki akses ke gudang produk ini.');
      }


      $messages = [
         'kode.max' => 'Kode barang maksimal 50 karakter.',
         'kode.unique' => 'Kode barang sudah digunakan produk lain.',
         'nama_produk.required' => 'Nama produk wajib diisi.',
         'nama_produk.string' => 'Nama produk harus berupa string.',
         'nama_produk.max' => 'Nama produk maksimal 50 karakter.',
         'nama_produk.unique' => 'Produk sudah ada.',
         'kategori.required' => 'Kategori wajib diisi!',
         'kategori.*.exists' => 'Kategori tidak ditemukan!',

      ];

      $validate = $request->validate([
         'kode' => [
            'nullable',
            'string',
            'max:50',
            Rule::unique('produk', 'kode')
               ->ignore($produkId)
               ->where(function ($q) use ($gudangId) {
                  return $q->where('gudang_id', $gudangId);
               }),
         ],
         'nama_produk' => [
            'required',
            'string',
            'max:50',
            Rule::unique('produk', 'nama_produk')
               ->ignore($produkId)
               ->where(function ($q) use ($gudangId) {
                  return $q->where('gudang_id', $gudangId);
               }),
         ],
         'kategori' => 'required|exists:kategori,id',
      ], $messages);

      // dd($tenant, $produk);
      if (! $produk) {
         return redirect()->route('admin.gudang')->with('error', 'Produk tidak ditemukan.');
      }

      // dd($request->all());
      $update = $produk->update([
         'kode' => $validate['kode'],
         'nama_produk' => $validate['nama_produk'],
         'kategori_id' => $validate['kategori'],
      ]);

      // dd($update);

      if (! $update) {
         return redirect()->route('admin.gudang')->with('error', 'Gagal mengupdate Produk');
      }

      return redirect()
         ->route('admin.gudang')
         ->with('success', 'Produk berhasil diupdate.');
   }

   public function deleteProdukGudang(Request $request, $id)
   {

      $tenant = $request->tenant;

      $produk = Produk::findOrFail($id);

      // 3️⃣ Ambil gudang produk
      $gudangId = $produk->gudang_id;

      // 4️⃣ Cek apakah tenant memiliki akses ke gudang produk
      $hasAccess = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->where('gudang_id', $gudangId)
         ->exists();

      if (! $hasAccess) {
         return redirect()->back()
            ->with('error', 'Produk tidak ditemukan di gudang ini.');
      }

      // 3️⃣ 🔥 CEK apakah produk sudah dipakai di produk_toko
      $isUsedInStore = DB::table('produk_toko')
         ->where('produk_id', $produk->id)
         ->exists();

      if ($isUsedInStore) {
         return redirect()->back()
            ->with('error', 'Produk tidak dapat dihapus karena sudah digunakan di toko.');
      }

      // 5️⃣ Hapus produk
      // (stok_gudang_masuk otomatis ikut terhapus karena cascade)
      $delete = $produk->delete();

      if (! $delete) {
         return redirect()->route('admin.gudang')->with('error', 'Gagal menghapus menu.');
      }

      return redirect()->route('admin.gudang')->with('success', 'Menu berhasil dihapus.');
   }

   public function historiStokGudangMasuk(Request $request)
   {
      $tenant = $request->tenant;
      $gudang = $tenant->gudangs;
      if ($gudang->isEmpty()) {
         return back()->with('error', 'Tenant ini tidak memiliki akses ke gudang manapun.');
      }

      // =========================
      // BULAN AKTIF (DEFAULT BULAN INI)
      // =========================
      $bulanAktif = $request->filled('bulan')
         ? Carbon::parse($request->bulan)
         : now();

      // =========================
      // BASE QUERY (SATU SUMBER)
      // =========================
      $baseQuery = StokGudangMasuk::whereHas(
         'gudang.tenants',
         function ($q) use ($tenant) {
            $q->where('tenants.id', $tenant->id);
         }
      )
         // ⬇️ SELALU FILTER BULAN
         ->whereMonth('created_at', $bulanAktif->month)
         ->whereYear('created_at', $bulanAktif->year)
         // ⬇️ FILTER PRODUK OPSIONAL
         ->when($request->filled('produk'), function ($q) use ($request) {
            $q->whereHas('produk', function ($p) use ($request) {
               $p->where('nama_produk', 'like', '%' . $request->produk . '%');
            });
         });

      // =========================
      // DATA RIWAYAT
      // =========================
      $riwayat = (clone $baseQuery)
         ->with(['produk', 'gudang', 'admin'])
         ->latest()
         ->paginate(10)
         ->withQueryString();

      // =========================
      // TOTAL (TIDAK TERPENGARUH PAGINATION)
      // =========================
      $total = (clone $baseQuery)
         ->selectRaw('
         COALESCE(SUM(qty_masuk), 0) as total_qty_masuk,
         COALESCE(SUM(harga_pembelian), 0) as total_harga_pembelian
      ')
         ->first();

      $title = $tenant->name . ' | Stok Gudang Masuk';

      return view(
         'pages.admin.stok-gudang-masuk.index',
         compact(
            'tenant',
            'gudang',
            'title',
            'riwayat',
            'total',
            'bulanAktif'
         )
      );
   }

   public function storeStokGudang(Request $request)
   {
      // 1️⃣ Ambil tenant aktif dari session
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang milik tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tidak ditemukan.');
      }

      // 3️⃣ Validasi input
      $messages = [
         'produk_id.required' => 'Produk wajib dipilih.',
         'produk_id.exists' => 'Produk tidak ditemukan.',
         'qty_masuk.required' => 'Jumlah stok wajib diisi.',
         'qty_masuk.integer' => 'Jumlah stok harus berupa angka.',
         'qty_masuk.min' => 'Jumlah stok minimal 1.',
         'harga_beli.required' => 'Harga pembelian wajib diisi.',
         'harga_beli.numeric' => 'Harga pembelian harus berupa angka.',
      ];

      $validated = $request->validate([
         'produk_id' => 'required|exists:produk,id',
         'qty_masuk' => 'required|integer|min:1',
         'harga_beli' => 'required|numeric',
      ], $messages);

      // 4️⃣ Ambil produk & pastikan milik gudang tenant
      $produk = Produk::where('id', $validated['produk_id'])
         ->where('gudang_id', $gudangId)
         ->first();

      if (! $produk) {
         return back()->with('error', 'Produk tidak ditemukan di gudang ini.');
      }

      // 5️⃣ Proses stok (TRANSACTION)
      DB::transaction(function () use ($validated, $produk, $gudangId) {

         // Update stok gudang
         $produk->increment('stok_gudang', $validated['qty_masuk']);

         // Simpan histori stok gudang masuk
         StokGudangMasuk::create([
            'gudang_id'        => $gudangId,
            'produk_id'        => $produk->id,
            'user_id'          => auth()->id(),
            'qty_masuk'        => $validated['qty_masuk'],
            'harga_pembelian' => str_replace('.', '', $validated['harga_beli']),
         ]);
      });

      return redirect()->route('admin.gudang')->with('success', 'Stok gudang berhasil ditambahkan.');
   }

   public function updateStokGudang(Request $request, $stokId)
   {
      $tenant = $request->tenant;

      // 3️⃣ Ambil gudang milik tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tenant tidak ditemukan.');
      }

      // 4️⃣ Validasi input
      $validated = $request->validate([
         'produk_id'  => 'required|exists:produk,id',
         'qty_masuk'  => 'required|integer|min:1',
         'harga_beli' => 'required|numeric|min:0',
      ], [
         'qty_masuk.required' => 'Jumlah stok wajib diisi.',
         'qty_masuk.integer'  => 'Jumlah stok harus berupa angka.',
         'qty_masuk.min'      => 'Stok minimal 1.',
         'harga_beli.required' => 'Harga pembelian wajib diisi.',
      ]);

      // 5️⃣ Ambil histori stok masuk
      $stokMasuk = StokGudangMasuk::where('id', $stokId)
         ->where('gudang_id', $gudangId)
         ->where('produk_id', $validated['produk_id'])
         ->first();

      if (! $stokMasuk) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Data stok masuk tidak ditemukan.');
      }

      if ($stokMasuk->created_at->addDays(3)->isPast()) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Data stok tidak bisa diubah karena sudah lebih dari 3 hari');
      }

      // 6️⃣ Ambil produk
      $produk = Produk::where('id', $validated['produk_id'])
         ->where('gudang_id', $gudangId)
         ->first();

      if (! $produk) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Produk tidak ditemukan di gudang ini.');
      }

      DB::transaction(function () use ($stokMasuk, $produk, $validated) {

         // 7️⃣ Hitung selisih stok
         $stokLama = $stokMasuk->qty_masuk;
         $stokBaru = $validated['qty_masuk'];
         $selisih  = $stokBaru - $stokLama;

         // 8️⃣ Update stok gudang produk
         $produk->stok_gudang += $selisih;
         $produk->save();

         // 9️⃣ Update histori stok masuk
         $stokMasuk->update([
            'qty_masuk'        => $stokBaru,
            'harga_pembelian' => str_replace('.', '', $validated['harga_beli']),
         ]);
      });

      return redirect()->route('admin.gudang.stok-masuk')->with('success', 'Stok gudang berhasil diperbarui.');
   }

   public function deleteStokGudang(Request $request, $stokId)
   {
      // 1️⃣ Ambil tenant
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tidak ditemukan.');
      }

      // 3️⃣ Ambil histori stok masuk
      $stokMasuk = StokGudangMasuk::where('id', $stokId)
         ->where('gudang_id', $gudangId)
         ->first();

      if (! $stokMasuk) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Data stok masuk tidak ditemukan.');
      }

      if ($stokMasuk->created_at->addDays(3)->isPast()) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Data stok tidak bisa dihapus karena sudah lebih dari 3 hari');
      }

      // 4️⃣ Ambil produk
      $produk = Produk::where('id', $stokMasuk->produk_id)
         ->where('gudang_id', $gudangId)
         ->first();

      if (! $produk) {
         return redirect()->route('admin.gudang.stok-masuk')->with('error', 'Produk tidak ditemukan.');
      }

      DB::transaction(function () use ($stokMasuk, $produk) {

         // 5️⃣ Kurangi stok gudang
         $produk->stok_gudang -= $stokMasuk->qty_masuk;

         if ($produk->stok_gudang < 0) {
            throw new \Exception('Stok gudang tidak mencukupi.');
         }

         $produk->save();

         // 6️⃣ Hapus histori stok masuk
         $stokMasuk->delete();
      });

      return redirect()->route('admin.gudang.stok-masuk')->with('success', 'Histori stok gudang berhasil dihapus.');
   }

   public function historiStokGudangKeluar(Request $request)
   {
      $tenant = $request->tenant;

      $gudang = $tenant->gudangs;
      if ($gudang->isEmpty()) {
         return back()->with('error', 'Tenant ini tidak memiliki akses ke gudang manapun.');
      }
      // =========================
      // BULAN AKTIF (DEFAULT BULAN INI)
      // =========================
      $bulanAktif = $request->filled('bulan')
         ? Carbon::parse($request->bulan)
         : now();

      // =========================
      // QUERY BASE
      // =========================
      $baseQuery = StokGudangKeluar::whereHas(
         'gudang.tenants',
         function ($q) use ($tenant) {
            $q->where('tenants.id', $tenant->id);
         }
      )
         ->whereMonth('created_at', $bulanAktif->month)
         ->whereYear('created_at', $bulanAktif->year);

      // =========================
      // FILTER PRODUK (OPSIONAL)
      // =========================
      if ($request->filled('produk')) {
         $baseQuery->whereHas('produk', function ($q) use ($request) {
            $q->where('nama_produk', 'like', '%' . $request->produk . '%');
         });
      }

      // =========================
      // RIWAYAT (PAGINATION)
      // =========================
      $riwayat = (clone $baseQuery)
         ->with(['produk', 'gudang', 'admin', 'stokToko'])
         ->latest()
         ->paginate(10)
         ->withQueryString();

      // =========================
      // TOTAL JUMLAH KELUAR
      // =========================
      $total = (clone $baseQuery)
         ->selectRaw('COALESCE(SUM(qty_keluar), 0) as total_jumlah_keluar')
         ->first();

      $title = $tenant->name . ' | Stok Gudang Keluar';

      return view(
         'pages.admin.stok-gudang-keluar.index',
         compact(
            'tenant',
            'riwayat',
            'title',
            'total',
            'bulanAktif',
            'gudang',
         )
      );
   }

   public function addStokGudangKeluar(Request $request)
   {
      $tenant = $request->tenant;

      $produkGudang = DB::table('produk')
         ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
         ->join('gudang_has_tenants', 'produk.gudang_id', '=', 'gudang_has_tenants.gudang_id')
         ->where('gudang_has_tenants.tenant_id', $tenant->id)
         ->orderBy('kategori.nama_kategori')
         ->orderBy('produk.nama_produk')
         ->select(
            'produk.id',
            'produk.nama_produk',
            'kategori.nama_kategori'
         )
         ->get()
         ->groupBy('nama_kategori');

      $title = $tenant->name . ' | Tambah Stok Keluar Gudang';
      // dd($tenant, $produkGudang);
      return view('pages.admin.stok-gudang-keluar.add', compact('tenant', 'produkGudang', 'title'));
   }

   public function storeStokGudangKeluar(Request $request)
   {
      // 1️⃣ Ambil tenant aktif
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang milik tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tidak ditemukan.');
      }

      // 3️⃣ Validasi input
      $validated = $request->validate([
         'produk'  => 'required|exists:produk,id',
         'qty_keluar' => 'required|integer|min:1',
         'keterangan' => 'required|string|max:255',
      ], [
         'produk.required'  => 'Produk wajib dipilih.',
         'qty_keluar.required' => 'Jumlah stok keluar wajib diisi.',
         'qty_keluar.min'      => 'Minimal stok keluar 1.',
      ]);

      try {
         DB::transaction(function () use ($validated, $gudangId) {

            $produk = Produk::lockForUpdate()
               ->where('id', $validated['produk'])
               ->where('gudang_id', $gudangId)
               ->first();

            if (! $produk) {
               throw ValidationException::withMessages([
                  'produk' => 'Produk tidak ditemukan di gudang.',
               ]);
            }

            if ($produk->stok_gudang < $validated['qty_keluar']) {
               throw ValidationException::withMessages([
                  'qty_keluar' => 'Stok keluar melebihi stok gudang.',
               ]);
            }

            // Kurangi stok
            $produk->update([
               'stok_gudang' => $produk->stok_gudang - $validated['qty_keluar'],
            ]);

            // Simpan histori
            DB::table('stok_gudang_keluar')->insert([
               'gudang_id'  => $gudangId,
               'produk_id'  => $produk->id,
               'qty_keluar' => $validated['qty_keluar'],
               'keterangan' => $validated['keterangan'],
               'user_id'    => auth()->id(),
               'created_at' => now(),
               'updated_at' => now(),
            ]);
         });
      } catch (ValidationException $e) {
         return back()
            ->withErrors($e->errors())
            ->withInput();
      }

      return redirect()->route('admin.gudang.stok-keluar')->with('success', 'Stok gudang keluar berhasil disimpan.');
   }

   public function editStokGudangKeluar($id)
   {
      $stokKeluar = StokGudangKeluar::with('produk')->findOrFail($id);
      $produk = $stokKeluar->produk;

      if (!$stokKeluar || !$produk) {
         return redirect()->back()->with('error', 'Data tidak ditemukan');
      }

      $tenant = DB::table('tenants')
         ->join('gudang_has_tenants', 'tenants.id', '=', 'gudang_has_tenants.tenant_id')
         ->join('stok_gudang_keluar', 'gudang_has_tenants.gudang_id', '=', 'stok_gudang_keluar.gudang_id')
         ->where('stok_gudang_keluar.id', $id)
         ->select('tenants.*')
         ->first();

      if (!$tenant) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan');
      }

      $produkGudang = DB::table('produk')
         ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
         ->join('gudang_has_tenants', 'produk.gudang_id', '=', 'gudang_has_tenants.gudang_id')
         ->where('gudang_has_tenants.tenant_id', $tenant->id)
         ->orderBy('kategori.nama_kategori')
         ->orderBy('produk.nama_produk')
         ->select(
            'produk.id',
            'produk.nama_produk',
            'kategori.nama_kategori'
         )
         ->get()
         ->groupBy('nama_kategori');

      $title = $tenant->name . ' | Edit Stok Keluar Gudang';
      // dd($id, $stokKeluar, $tenant, $produkGudang);
      return view('pages.admin.stok-gudang-keluar.edit', compact('stokKeluar', 'produkGudang', 'tenant', 'title', 'produk'));
   }

   public function updateStokGudangKeluar(Request $request, $id)
   {
      $validated = $request->validate([
         'qty_keluar' => 'required|integer|min:1',
         'keterangan' => 'required|string|max:255',
      ]);

      try {
         DB::transaction(function () use ($validated, $id) {

            // =========================
            // 1️⃣ TENANT AKTIF
            // =========================
            $tenantId = session('active_tenant_id');

            if (! $tenantId) {
               abort(403, 'Tenant aktif tidak ditemukan.');
            }

            $gudangId = DB::table('gudang_has_tenants')
               ->where('tenant_id', $tenantId)
               ->value('gudang_id');

            if (! $gudangId) {
               abort(403, 'Gudang tenant tidak ditemukan.');
            }

            // =========================
            // 3️⃣ AMBIL STOK KELUAR (LOCK)
            // =========================
            $stokKeluar = StokGudangKeluar::lockForUpdate()->findOrFail($id);

            // =========================
            // 4️⃣ VALIDASI KEPEMILIKAN GUDANG
            // =========================
            if ($stokKeluar->gudang_id != $gudangId) {
               abort(403, 'Anda tidak memiliki akses ke stok gudang ini.');
            }


            $qtyLama = $stokKeluar->qty_keluar;
            $qtyBaru = $validated['qty_keluar'];
            $selisih = $qtyBaru - $qtyLama;

            // 2️⃣ Ambil produk gudang (LOCK)
            $produk = Produk::lockForUpdate()
               ->where('id', $stokKeluar->produk_id)
               ->where('gudang_id', $stokKeluar->gudang_id)
               ->firstOrFail();

            /**
             * LOGIKA STOK
             * selisih > 0  → stok gudang BERKURANG
             * selisih < 0  → stok gudang BERTAMBAH
             */
            if ($selisih > 0 && $produk->stok_gudang < $selisih) {
               throw ValidationException::withMessages([
                  'qty_keluar' => 'Stok gudang tidak mencukupi.',
               ]);
            }

            // 3️⃣ Update stok gudang
            $produk->update([
               'stok_gudang' => $produk->stok_gudang - $selisih,
            ]);

            // 4️⃣ Update histori stok keluar
            $stokKeluar->update([
               'qty_keluar' => $qtyBaru,
               'keterangan' => $validated['keterangan'],
            ]);
         });
      } catch (ValidationException $e) {
         return back()
            ->withErrors($e->errors())
            ->withInput();
      }

      return redirect()
         ->route('admin.gudang.stok-keluar')
         ->with('success', 'Stok gudang keluar berhasil diperbarui.');
   }

   public function deleteStokGudangKeluar($id)
   {
      DB::transaction(function () use ($id) {

         $tenantId = session('active_tenant_id');

         if (! $tenantId) {
            abort(403, 'Tenant aktif tidak ditemukan.');
         }
         // 1️⃣ Ambil data stok gudang keluar
         $stokKeluar = DB::table('stok_gudang_keluar')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

         if (! $stokKeluar) {
            abort(403, 'Data stok gudang keluar tidak ditemukan.');
         }

         $hasAccess = DB::table('gudang_has_tenants')
            ->where('tenant_id', $tenantId)
            ->where('gudang_id', $stokKeluar->gudang_id)
            ->exists();

         if (! $hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini.');
         }

         // 2️⃣ Ambil produk terkait (gudang)
         $produk = Produk::lockForUpdate()
            ->where('id', $stokKeluar->produk_id)
            ->where('gudang_id', $stokKeluar->gudang_id)
            ->first();

         if (! $produk) {
            return back()->with('error', 'Produk gudang tidak ditemukan.');
         }

         // 3️⃣ Kembalikan stok ke gudang
         $produk->update([
            'stok_gudang' => $produk->stok_gudang + $stokKeluar->qty_keluar,
         ]);

         // 4️⃣ Hapus histori stok gudang keluar
         DB::table('stok_gudang_keluar')
            ->where('id', $stokKeluar->id)
            ->delete();
      });

      return redirect()
         ->route('admin.gudang.stok-keluar')
         ->with('success', 'Stok gudang keluar berhasil dihapus dan stok dikembalikan.');
   }

   public function logGudang(Request $request)
   {
      $tenant = $request->tenant;
      $tenantId = $tenant->id;
      // 🔐 Gudang tenant
      $gudang = Gudang::whereHas('tenants', function ($q) use ($tenantId) {
         $q->where('tenant_id', $tenantId);
      })->firstOrFail();
      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->toDateString()
         : now()->toDateString();

      // =========================
      // QUERY LOG GUDANG (PER PRODUK)
      // =========================
      $log = Produk::where('gudang_id', $gudang->id)
         ->with('gudang')
         ->select([
            'produk.id',
            'produk.nama_produk',
            'produk.stok_gudang',
            'produk.gudang_id',
         ])
         ->selectSub(function ($q) use ($tanggalAktif) {
            $q->from('stok_gudang_masuk')
               ->selectRaw('COALESCE(SUM(qty_masuk), 0)')
               ->whereColumn('stok_gudang_masuk.produk_id', 'produk.id')
               ->whereDate('stok_gudang_masuk.created_at', $tanggalAktif);
         }, 'total_masuk')
         ->selectSub(function ($q) use ($tanggalAktif) {
            $q->from('stok_gudang_keluar')
               ->selectRaw('COALESCE(SUM(qty_keluar), 0)')
               ->whereColumn('stok_gudang_keluar.produk_id', 'produk.id')
               ->whereDate('stok_gudang_keluar.created_at', $tanggalAktif);
         }, 'total_keluar')
         ->orderBy('produk.nama_produk')
         ->get();

      $title = $tenant->name . ' | Log Gudang';

      return view(
         'pages.admin.gudang.log',
         compact('tenant', 'gudang', 'title', 'log', 'tanggalAktif')
      );
   }

   public function detailLogGudang(Request $request, $produkId)
   {
      // =========================
      // VALIDASI TENANT
      // =========================
      $tenant = $request->tenant;

      if (! $tenant->isActive) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak aktif.');
      }
      $tenantId = $tenant->id;
      // 🔐 Gudang tenant
      $gudang = Gudang::whereHas('tenants', function ($q) use ($tenantId) {
         $q->where('tenant_id', $tenantId);
      })->firstOrFail();

      // =========================
      // VALIDASI PRODUK MILIK GUDANG TENANT
      // =========================
      $produk = Produk::where('id', $produkId)
         ->where('gudang_id', $gudang->id)
         ->firstOrFail();

      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->toDateString()
         : now()->toDateString();

      // =========================
      // AMBIL LOG MASUK & KELUAR
      // =========================
      $stokMasuk = DB::table('stok_gudang_masuk')
         ->where('produk_id', $produk->id)
         ->whereDate('created_at', $tanggalAktif)
         ->select(
            'created_at',
            DB::raw('qty_masuk as masuk'),
            DB::raw('0 as keluar')
         );

      $stokKeluar = DB::table('stok_gudang_keluar')
         ->where('produk_id', $produk->id)
         ->whereDate('created_at', $tanggalAktif)
         ->select(
            'created_at',
            DB::raw('0 as masuk'),
            DB::raw('qty_keluar as keluar')
         );

      $log = $stokMasuk
         ->unionAll($stokKeluar)
         ->orderBy('created_at')
         ->get();

      // =========================
      // HITUNG TOTAL HARI ITU
      // =========================
      $totalMasuk = $log->sum('masuk');
      $totalKeluar = $log->sum('keluar');

      // =========================
      // HITUNG STOK AWAL HARI
      // =========================
      $stokAwal = $produk->stok_gudang
         - $totalMasuk
         + $totalKeluar;

      // =========================
      // HITUNG STOK BERJALAN
      // =========================
      $stokBerjalan = $stokAwal;

      $logFinal = $log->map(function ($item) use (&$stokBerjalan) {
         $stokBerjalan =
            $stokBerjalan
            + $item->masuk
            - $item->keluar;

         return [
            'jam'    => Carbon::parse($item->created_at)->format('H:i:s'),
            'masuk'  => $item->masuk,
            'keluar' => $item->keluar,
            'stok'   => $stokBerjalan,
         ];
      });

      $title = $tenant->name . ' | Detail Log Gudang';

      return view(
         'pages.admin.gudang.detail-log',
         compact(
            'tenant',
            'produk',
            'logFinal',
            'tanggalAktif',
            'title'
         )
      );
   }

   public function produkToko(Request $request)
   {
      // 1️⃣ Tenant aktif
      $tenant = $request->tenant;

      // 2️⃣ Ambil gudang tenant
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tidak ditemukan untuk tenant ini.');
      }

      // 3️⃣ Ambil semua produk gudang + relasi produk_toko (LEFT JOIN)
      $produk = Produk::where('gudang_id', $gudangId)
         ->with([
            'produkToko' => function ($q) use ($tenant) {
               $q->where('tenant_id', $tenant->id)
                  ->withCount([
                     'varians',
                     'varians as varians_aktif_count' => function ($q) {
                        $q->where('is_active', true);
                     }
                  ]);
            }
         ])
         ->get()
         ->map(function ($item) {

            $produkToko = $item->produkToko->first();

            $item->toko = (object) [
               'stok'       => $produkToko->stok ?? 0,
               'display'    => (bool) ($produkToko->display ?? false),
               'exists'     => ! is_null($produkToko),
               'jumlah_varian' => $produkToko->varians_count ?? 0,
               'varian_aktif'  => $produkToko->varians_aktif_count ?? 0,
            ];

            return $item;
         });

      // $kategori = Kategori::all();
      $title = $tenant->name . ' | Produk';

      return view(
         'pages.admin.produk-toko.index',
         compact('tenant', 'produk', 'title')
      );
   }

   public function getVarian($produkTokoId)
   {
      return ProdukVarian::where('produk_toko_id', $produkTokoId)->get();
   }

   public function storeOrUpdate(Request $request)
   {
      $request->validate([
         'produk_toko_id' => 'required|exists:produk_toko,id',
         'varians' => 'required|array|min:1',
         'varians.*.nama' => 'required|string|max:255',
         'varians.*.harga_jual' => 'required|numeric|min:0',
      ]);

      DB::transaction(function () use ($request) {

         $produkToko = ProdukToko::lockForUpdate()
            ->findOrFail($request->produk_toko_id);

         $variansRequest = collect($request->varians);

         // 1️⃣ Validasi minimal 1 varian aktif
         $aktifCount = $variansRequest
            ->filter(fn($v) => isset($v['is_active']))
            ->count();

         if ($aktifCount < 1) {
            abort(422, 'Produk harus memiliki minimal 1 varian aktif');
         }

         // 2️⃣ Ambil semua varian lama (untuk deteksi nonaktif)
         $existingVarians = ProdukVarian::where('produk_toko_id', $produkToko->id)
            ->get()
            ->keyBy('id');

         $processedIds = [];

         // 3️⃣ Loop varians dari form
         foreach ($variansRequest as $data) {

            // pastikan harga bersih (frontend sudah bersihin, ini pengaman)
            $harga = (float) preg_replace('/[^0-9]/', '', $data['harga_jual']);

            if (!empty($data['id']) && $existingVarians->has($data['id'])) {
               // UPDATE
               $varian = $existingVarians[$data['id']];
               $varian->update([
                  'nama' => $data['nama'],
                  'harga_jual' => $harga,
                  'is_active' => isset($data['is_active']),
               ]);

               $processedIds[] = $varian->id;
            } else {
               // CREATE
               $varian = ProdukVarian::create([
                  'produk_toko_id' => $produkToko->id,
                  'nama' => $data['nama'],
                  'harga_jual' => $harga,
                  'is_active' => isset($data['is_active']),
               ]);

               $processedIds[] = $varian->id;
            }
         }

         // 4️⃣ Nonaktifkan varian lama yang tidak ada di form
         ProdukVarian::where('produk_toko_id', $produkToko->id)
            ->whereNotIn('id', $processedIds)
            ->update(['is_active' => false]);

         // 5️⃣ Update display produk_toko
         $produkToko->update([
            'display' => $aktifCount > 0
         ]);
      });

      return response()->json([
         'success' => true,
         'message' => 'Varian berhasil disimpan'
      ]);
   }

   public function editHargaProdukToko(Request $request, $id)
   {
      // dd($id);
      // 1️⃣ Ambil tenant aktif
      $tenant = $request->tenant;

      // 2️⃣ Ambil produk toko
      $produkToko = ProdukToko::where('tenant_id', $tenant->id)
         ->where('produk_id', $id)
         ->firstOrFail();
      // dd($produkToko);

      // update hatga jual produk toko
      $produkToko->update([
         'harga_jual' => str_replace('.', '', $request->harga_jual),
      ]);

      return back()->with('success', 'Edit harga produk toko berhasil.');
   }

   public function storeStokToko(Request $request)
   {
      // =========================
      // 1️⃣ AMBIL TENANT AKTIF
      // =========================
      $tenant = $request->tenant;

      // =========================
      // 2️⃣ AMBIL GUDANG TENANT
      // =========================
      $gudangId = DB::table('gudang_has_tenants')
         ->where('tenant_id', $tenant->id)
         ->value('gudang_id');

      if (! $gudangId) {
         return back()->with('error', 'Gudang tidak ditemukan.');
      }

      // =========================
      // 3️⃣ VALIDASI INPUT
      // =========================
      $validated = $request->validate([
         'produk_id'  => 'required|exists:produk,id',
         'stok_masuk' => 'required|integer|min:1',
      ]);

      // =========================
      // 4️⃣ AMBIL PRODUK GUDANG
      // =========================
      $produk = Produk::where('id', $validated['produk_id'])
         ->where('gudang_id', $gudangId)
         ->lockForUpdate()
         ->first();

      if (! $produk) {
         return redirect()->route('admin.produk')->with('error', 'Produk tidak ditemukan di gudang.');
      }

      // =========================
      // 5️⃣ CEK STOK GUDANG
      // =========================
      if ($produk->stok_gudang < $validated['stok_masuk']) {
         return back()->with('error', 'Stok gudang tidak mencukupi.');
      }

      $produkTokoExists = ProdukToko::where('tenant_id', $tenant->id)
         ->where('produk_id', $validated['produk_id'])
         ->exists();

      if (! $produkTokoExists) {
         $request->validate([
            'harga_jual' => 'required|numeric|min:0',
         ], [
            'harga_jual.required' => 'Harga jual wajib diisi untuk produk baru.',
         ]);
      }
      // =========================
      // 6️⃣ TRANSAKSI UTAMA
      // =========================
      DB::transaction(function () use ($tenant, $produk, $validated, $gudangId, $request) {
         // =========================
         // 6.1️⃣ STOK GUDANG KELUAR
         // =========================
         $stokGudangKeluar = StokGudangKeluar::create([
            'gudang_id'  => $gudangId,
            'produk_id'  => $produk->id,
            'qty_keluar' => $validated['stok_masuk'],
            'keterangan' => 'Distribusi ke toko ' . $tenant->name,
            'user_id'    => auth()->id(),
         ]);

         // =========================
         // 6.2️⃣ KURANGI STOK GUDANG
         // =========================
         $produk->decrement('stok_gudang', $validated['stok_masuk']);

         // =========================
         // 6.3️⃣ PRODUK TOKO
         // =========================
         $produkToko = ProdukToko::where('tenant_id', $tenant->id)
            ->where('produk_id', $produk->id)
            ->lockForUpdate()
            ->first();

         // =========================
         // 6.4️⃣ JIKA BELUM ADA → CREATE + VARIAN DEFAULT
         // =========================
         if (! $produkToko) {

            $produkToko = ProdukToko::create([
               'tenant_id' => $tenant->id,
               'produk_id' => $produk->id,
               'stok'      => $validated['stok_masuk'],
               'display'   => true,
            ]);

            ProdukVarian::create([
               'produk_toko_id' => $produkToko->id,
               'nama'           => $produk->nama_produk,
               'harga_jual'     => preg_replace('/[^0-9]/', '', $request->harga_jual),
               'is_active'      => true,
            ]);
         } else {
            // =========================
            // 6.5️⃣ JIKA SUDAH ADA → TAMBAH STOK SAJA
            // =========================
            $produkToko->update([
               'display' => 1,
            ]);
            $produkToko->increment('stok', $validated['stok_masuk']);
         }

         // =========================
         // 6.6️⃣ STOK MASUK TOKO
         // =========================
         Stok::create([
            'tenant_id'             => $tenant->id,
            'produk_id'             => $produk->id,
            'stok_masuk'            => $validated['stok_masuk'],
            'stok_gudang_keluar_id' => $stokGudangKeluar->id,
            'admin_id'              => auth()->id(),
         ]);
      });

      return redirect()->route('admin.produk')->with('success', 'Stok berhasil dipindahkan ke toko.');
   }

   public function updateStokToko(Request $request, $id)
   {
      $tenantId = session('active_tenant_id');

      $validated = $request->validate([
         'stok_masuk' => 'required|integer|min:1',
      ]);

      DB::transaction(function () use ($id, $tenantId, $validated) {

         // =========================
         // 1️⃣ Ambil histori stok masuk toko
         // =========================
         $stok = Stok::with('stokGudangKeluar')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->lockForUpdate()
            ->firstOrFail();

         if ($stok->created_at->addDays(3)->isPast()) {
            return redirect()->route('admin.stok')->with('error', 'Data stok tidak bisa diubah karena sudah lebih dari 3 hari');
         }

         $stokLama = $stok->stok_masuk;
         $stokBaru = $validated['stok_masuk'];
         $selisih  = $stokBaru - $stokLama;

         // =========================
         // 2️⃣ Ambil produk gudang
         // =========================
         $produk = Produk::lockForUpdate()
            ->findOrFail($stok->produk_id);

         // =========================
         // 3️⃣ Validasi stok gudang
         // =========================
         if ($selisih > 0 && $produk->stok_gudang < $selisih) {
            throw new \Exception('Stok gudang tidak mencukupi.');
         }

         // =========================
         // 4️⃣ Update stok gudang
         // =========================
         $produk->update([
            'stok_gudang' => $produk->stok_gudang - $selisih
         ]);

         // =========================
         // 5️⃣ UPDATE PRODUK TOKO (STOK + HARGA)
         // =========================
         $produkToko = ProdukToko::where('tenant_id', $tenantId)
            ->where('produk_id', $produk->id)
            ->lockForUpdate()
            ->firstOrFail();

         $produkToko->update([
            'stok'       => $produkToko->stok + $selisih,
         ]);

         // =========================
         // 6️⃣ Update histori stok toko
         // =========================
         $stok->update([
            'stok_masuk' => $stokBaru,
         ]);

         // =========================
         // 7️⃣ Update stok gudang keluar
         // =========================
         if ($stok->stokGudangKeluar) {
            $stok->stokGudangKeluar->update([
               'qty_keluar' => $stokBaru,
            ]);
         }
         return redirect()->route('admin.stok')->with('success', 'Stok & harga jual berhasil diperbarui.');
      });
      return redirect()->route('admin.stok');
   }

   public function deleteStokToko($id)
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return back()->with('error', 'Tenant tidak aktif.');
      }

      try {

         DB::transaction(function () use ($id, $tenantId) {

            // =========================
            // 1️⃣ Ambil stok masuk toko
            // =========================
            $stok = Stok::with('stokGudangKeluar')
               ->where('id', $id)
               ->where('tenant_id', $tenantId)
               ->lockForUpdate()
               ->firstOrFail();

            if ($stok->created_at->addDays(3)->isPast()) {
               return redirect()->route('admin.stok')->with('error', 'Data stok tidak bisa dihapus karena sudah lebih dari 3 hari');
            }

            $qty = $stok->stok_masuk;

            // =========================
            // 2️⃣ Ambil produk (lock)
            // =========================
            $produk = Produk::lockForUpdate()
               ->findOrFail($stok->produk_id);

            // =========================
            // 3️⃣ Ambil produk toko (pivot)
            // =========================
            $produkToko = DB::table('produk_toko')
               ->where('tenant_id', $tenantId)
               ->where('produk_id', $produk->id)
               ->lockForUpdate()
               ->first();

            if (! $produkToko) {
               throw new \Exception('Produk tidak terdaftar di toko.');
            }

            // 🔒 Proteksi stok minus
            if ($produkToko->stok < $qty) {
               throw new \Exception('Stok toko tidak mencukupi.');
            }

            // =========================
            // 4️⃣ Kurangi stok toko
            // =========================
            DB::table('produk_toko')
               ->where('id', $produkToko->id)
               ->update([
                  'stok' => $produkToko->stok - $qty,
                  'updated_at' => now(),
               ]);

            // =========================
            // 5️⃣ Kembalikan stok gudang
            // =========================
            $produk->update([
               'stok_gudang' => $produk->stok_gudang + $qty,
            ]);

            // =========================
            // 6️⃣ Hapus stok gudang keluar (jika ada)
            // =========================
            if ($stok->stokGudangKeluar) {
               $stok->stokGudangKeluar->delete();
            }

            // =========================
            // 7️⃣ Hapus histori stok toko
            // =========================
            $stok->delete();

            // ✅ hanya dieksekusi jika TIDAK ada error
            return redirect()->route('admin.stok')->with('success', 'Stok toko berhasil dihapus dan stok gudang dikembalikan.');
         });
      } catch (\Throwable $e) {

         // ⚠️ rollback otomatis oleh transaction
         return back()
            ->withInput()
            ->with('error', $e->getMessage());
      }

      // ✅ hanya dieksekusi jika TIDAK ada error
      return redirect()->route('admin.stok');
   }


   public function toggleDisplayProdukToko($produkId)
   {
      $tenantId = session('active_tenant_id');

      if (! $tenantId) {
         return back()->with('error', 'Tenant tidak aktif.');
      }

      $produkToko = DB::table('produk_toko')
         ->where('tenant_id', $tenantId)
         ->where('produk_id', $produkId)
         ->first();

      // ❗ Jika BELUM PERNAH DITAMPILKAN
      if (! $produkToko) {
         DB::table('produk_toko')->insert([
            'tenant_id'  => $tenantId,
            'produk_id'  => $produkId,
            'stok'       => 0,
            'harga_jual' => 0,
            'display' => 1,
            'created_at' => now(),
            'updated_at' => now(),
         ]);

         return back()->with('success', 'Produk berhasil ditampilkan.');
      }

      // 🔁 Toggle display
      DB::table('produk_toko')
         ->where('tenant_id', $tenantId)
         ->where('produk_id', $produkId)
         ->update([
            'display' => ! $produkToko->display,
            'updated_at' => now(),
         ]);

      return back()->with('success', 'Status display produk berhasil diubah.');
   }

   public function stok(Request $request)
   {
      $tenant = $request->tenant;
      // cek status tenant dan batasi agar tidak dapat masuk ke tanant yg tidak aktif
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }
      // =========================
      // BULAN AKTIF (DEFAULT: BULAN INI)
      // =========================
      $bulanAktif = $request->filled('bulan')
         ? Carbon::parse($request->bulan)
         : now();

      // =========================
      // QUERY STOK TOKO KELUAR
      // =========================
      $stok = Stok::with(['produk', 'user'])
         ->where('tenant_id', $tenant->id)
         ->whereMonth('created_at', $bulanAktif->month)
         ->whereYear('created_at', $bulanAktif->year)
         ->latest()
         ->get();
      $title = $tenant->name . ' | Stok Masuk';
      return view('pages.admin.stok-toko.index', compact('tenant', 'stok', 'title'));
   }

   public function stokTokoKeluar(Request $request)
   {
      // =========================
      // TENANT
      // =========================
      $tenant = $request->tenant;

      if (! $tenant->isActive) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $searchDate = $request->search_date; // format: YYYY-MM-DD

      // =========================
      // QUERY STOK TOKO KELUAR
      // =========================
      $tanggalAktif = $request->filled('search_date')
         ? Carbon::parse($request->search_date)
         : now();

      // =========================
      // QUERY STOK TOKO KELUAR
      // =========================
      $stokKeluar = StokTokoKeluar::with(['produkVarian', 'user'])
         ->where('tenant_id', $tenant->id)
         ->whereDate('created_at', [$tanggalAktif])
         ->orderBy('created_at', 'desc')
         ->get();
      // dd($stokKeluar);
      // dd($tenant, $bulanAktif, $stokKeluar);
      // =========================
      // TITLE
      // =========================
      $title = $tenant->name . ' | Stok Keluar';

      return view(
         'pages.admin.stok-toko-keluar.index',
         compact('tenant', 'stokKeluar', 'title', 'tanggalAktif')
      );
   }

   public function addStokKeluar(Request $request)
   {
      // =========================
      // AMBIL TENANT
      // =========================
      $tenant = $request->tenant;

      // =========================
      // CEK TENANT AKTIF
      // =========================
      if (! $tenant->isActive) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak aktif.');
      }

      // =========================
      // PRODUK YANG DIJUAL DI TOKO
      // (BERDASARKAN TABEL produk_toko)
      // =========================
      $produkToko = DB::table('produk_varian')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('produk_toko.tenant_id', $tenant->id)
         ->select(
            'produk_varian.id as produk_varian_id',
            'produk.nama_produk',
            'produk_varian.nama as nama_varian'
         )
         ->get()
         ->groupBy('nama_produk');
      // jika pakai kategori
      // =========================
      // JUDUL HALAMAN
      // =========================
      $title = $tenant->name . ' | Tambah Stok Toko Keluar';

      return view(
         'pages.admin.stok-toko-keluar.add',
         compact('tenant', 'produkToko', 'title')
      );
   }

   public function storeStokTokoKeluar(Request $request)
   {
      $tenant   = $request->tenant;
      $tenantId = $tenant->id;

      $request->validate([
         'produk' => [
            'required',
            Rule::exists('produk_varian', 'id'),
         ],
         'qty_keluar' => 'required|integer|min:1',
         'keterangan' => 'required|string',
      ]);

      DB::transaction(function () use ($request, $tenantId) {

         // =========================
         // AMBIL PRODUK VARIAN
         // =========================
         $produkVarian = DB::table('produk_varian')
            ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
            ->where('produk_varian.id', $request->produk)
            ->where('produk_toko.tenant_id', $tenantId)
            ->select(
               'produk_varian.id as produk_varian_id',
               'produk_varian.harga_jual',
               'produk_toko.id as produk_toko_id',
               'produk_toko.stok'
            )
            ->lockForUpdate()
            ->first();

         if (! $produkVarian) {
            return redirect()->route('admin.stok-keluar')->with('Produk varian tidak ditemukan.');
         }

         // =========================
         // CEK STOK TOKO (INDUK)
         // =========================
         if ($produkVarian->stok < $request->qty_keluar) {
            return redirect()->route('admin.stok-keluar')->with('error', 'Stok toko tidak mencukupi.');
         }

         // =========================
         // SIMPAN STOK TOKO KELUAR
         // =========================
         DB::table('stok_toko_keluar')->insert([
            'tenant_id'        => $tenantId,
            'kasir_id'         => auth()->id(),
            'produk_varian_id' => $produkVarian->produk_varian_id,
            'jumlah_retur'     => $request->qty_keluar,
            'harga_produk'     => $produkVarian->harga_jual,
            'keterangan'       => $request->keterangan,
            'created_at'       => now(),
            'updated_at'       => now(),
         ]);

         // =========================
         // KURANGI STOK TOKO (INDUK)
         // =========================
         DB::table('produk_toko')
            ->where('id', $produkVarian->produk_toko_id)
            ->update([
               'stok' => $produkVarian->stok - $request->qty_keluar,
               'updated_at' => now(),
            ]);

         return redirect()
            ->route('admin.stok-keluar')
            ->with('success', 'Stok keluar toko berhasil disimpan.');
      });

      // dd($store);

      return redirect()
         ->route('admin.stok-keluar');
   }

   public function updateStokTokoKeluar(Request $request, $id)
   {
      $request->validate([
         'qty_keluar' => 'required|integer|min:1',
         'keterangan' => 'required|string|max:255',
      ]);

      try {
         DB::transaction(function () use ($request, $id) {

            // =========================
            // LOCK STOK TOKO KELUAR
            // =========================
            $stokKeluar = StokTokoKeluar::lockForUpdate()->findOrFail($id);

            if ($stokKeluar->created_at->addDays(3)->isPast()) {
               throw new \Exception('error', 'Data stok tidak bisa diubah karena sudah lebih dari 3 hari');
            }

            // =========================
            // LOCK PRODUK VARIAN
            // =========================
            $produkVarian = DB::table('produk_varian')
               ->where('id', $stokKeluar->produk_varian_id)
               ->lockForUpdate()
               ->first();

            if (! $produkVarian) {
               throw new \Exception('Produk varian tidak ditemukan.');
            }

            // =========================
            // LOCK PRODUK TOKO (STOK FISIK)
            // =========================
            $produkToko = DB::table('produk_toko')
               ->where('id', $produkVarian->produk_toko_id)
               ->lockForUpdate()
               ->first();

            if (! $produkToko) {
               return redirect()->route('admin.stok-keluar')->with('Produk toko tidak ditemukan.');
            }

            // =========================
            // HITUNG SELISIH (SATU-SATUNYA SUMBER)
            // =========================
            $qtyLama = $stokKeluar->jumlah_retur;
            $qtyBaru = $request->qty_keluar;
            $selisih = $qtyBaru - $qtyLama;
            // dd($qtyLama, $qtyBaru, $selisih);

            // =========================
            // VALIDASI STOK TOKO
            // =========================
            if ($selisih > 0 && $produkToko->stok < $selisih) {
               throw new \Exception('Stok toko tidak mencukupi.');
            }

            // =========================
            // UPDATE STOK KELUAR
            // =========================
            $stokKeluar->update([
               'jumlah_retur' => $qtyBaru,
               'keterangan'    => $request->keterangan,
            ]);

            // =========================
            // UPDATE STOK TOKO (1 KALI SAJA)
            // =========================
            DB::table('produk_toko')
               ->where('id', $produkToko->id)
               ->update([
                  'stok'       => $produkToko->stok - $selisih,
                  'updated_at' => now(),
               ]);
         });

         return redirect()
            ->route('admin.stok-keluar')
            ->with('success', 'Stok keluar toko berhasil diupdate.');
      } catch (\Throwable $e) {
         return redirect()->route('admin.stok-keluar')->with('error', $e->getMessage());
      }
   }


   public function deleteStokTokoKeluar($id)
   {
      try {
         DB::transaction(function () use ($id) {

            // =========================
            // LOCK STOK TOKO KELUAR
            // =========================
            $stokKeluar = StokTokoKeluar::lockForUpdate()
               ->findOrFail($id);

            if ($stokKeluar->created_at->addDays(3)->isPast()) {
               throw new \Exception('error', 'Data stok tidak bisa dihapus karena sudah lebih dari 3 hari');
            }

            // =========================
            // LOCK PRODUK VARIAN
            // =========================
            $produkVarian = DB::table('produk_varian')
               ->where('id', $stokKeluar->produk_varian_id)
               ->lockForUpdate()
               ->first();

            if (! $produkVarian) {
               throw new \Exception('Produk varian tidak ditemukan.');
            }

            // =========================
            // LOCK PRODUK TOKO (STOK FISIK)
            // =========================
            $produkToko = DB::table('produk_toko')
               ->where('id', $produkVarian->produk_toko_id)
               ->lockForUpdate()
               ->first();
            // dd($produkToko);

            if (! $produkToko) {
               throw new \Exception('Produk toko tidak ditemukan.');
            }

            // =========================
            // QTY YANG DIKEMBALIKAN
            // =========================
            $qtyKeluar = $stokKeluar->jumlah_retur;

            // =========================
            // KEMBALIKAN STOK KE TOKO
            // =========================
            DB::table('produk_toko')
               ->where('id', $produkToko->id)
               ->update([
                  'stok'       => $produkToko->stok + $qtyKeluar,
                  'updated_at' => now(),
               ]);

            // =========================
            // HAPUS DATA STOK KELUAR
            // =========================
            $stokKeluar->delete();

            return redirect()
               ->route('admin.stok-keluar')
               ->with('success', 'Stok keluar toko berhasil dihapus.');
         });

         return redirect()
            ->route('admin.stok-keluar');
      } catch (\Throwable $e) {
         return redirect()->route('admin.stok-keluar')->with('error', $e->getMessage());
      }
   }


   public function logToko(Request $request)
   {
      // =========================
      // CEK TENANT
      // =========================
      $tenant = $request->tenant;

      if (! $tenant->isActive) {
         return redirect()->route('admin.index')
            ->with('error', 'Tenant tidak aktif.');
      }

      // =========================
      // TANGGAL AKTIF
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->format('Y-m-d')
         : now()->format('Y-m-d');

      /**
     |--------------------------------------------------------------------------
     | SUBQUERY STOK MASUK TOKO (BERDASARKAN PRODUK_VARIAN)
     |--------------------------------------------------------------------------
       */
      $stokMasuk = DB::table('stok')
         ->join('produk_toko', function ($join) {
            $join->on('stok.produk_id', '=', 'produk_toko.produk_id')
               ->on('stok.tenant_id', '=', 'produk_toko.tenant_id');
         })
         ->select(
            'produk_toko.id as produk_toko_id',
            DB::raw('SUM(stok.stok_masuk) as total_masuk')
         )
         ->where('stok.tenant_id', $tenant->id)
         ->whereDate('stok.created_at', $tanggalAktif)
         ->groupBy('produk_toko.id');

      /**
     |--------------------------------------------------------------------------
     | SUBQUERY STOK KELUAR / RETUR
     |--------------------------------------------------------------------------
       */
      $stokKeluar = DB::table('stok_toko_keluar')
         ->select(
            'produk_varian_id',
            DB::raw('SUM(jumlah_retur) as total_keluar')
         )
         ->where('tenant_id', $tenant->id)
         ->whereDate('created_at', $tanggalAktif)
         ->groupBy('produk_varian_id');

      /**
     |--------------------------------------------------------------------------
     | SUBQUERY PRODUK TERJUAL
     |--------------------------------------------------------------------------
       */
      $produkTerjual = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->select(
            'detail_transaksis.produk_varian_id',
            DB::raw('SUM(detail_transaksis.jumlah) as total_terjual')
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereDate('transaksis.created_at', $tanggalAktif)
         ->groupBy('detail_transaksis.produk_varian_id');

      /**
     |--------------------------------------------------------------------------
     | QUERY FINAL LOG TOKO (PRODUK VARIAN)
     |--------------------------------------------------------------------------
       */
      $logStok = DB::table('produk_varian')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')

         ->leftJoinSub($stokMasuk, 'masuk', function ($join) {
            $join->on('produk_toko.id', '=', 'masuk.produk_toko_id');
         })
         ->leftJoinSub($produkTerjual, 'terjual', function ($join) {
            $join->on('produk_varian.id', '=', 'terjual.produk_varian_id');
         })
         ->leftJoinSub($stokKeluar, 'keluar', function ($join) {
            $join->on('produk_varian.id', '=', 'keluar.produk_varian_id');
         })

         ->where('produk_toko.tenant_id', $tenant->id)
         ->where('produk_varian.is_active', true)

         ->select(
            'produk_varian.id as produk_varian_id',
            'produk.nama_produk',
            'produk_varian.nama as varian',

            DB::raw('COALESCE(masuk.total_masuk, 0) as total_masuk'),
            DB::raw('COALESCE(terjual.total_terjual, 0) as total_terjual'),
            DB::raw('COALESCE(keluar.total_keluar, 0) as total_keluar'),

            'produk_toko.stok as stok_saat_ini'
         )
         ->orderBy('produk.nama_produk')
         ->orderBy('produk_varian.nama')
         ->get();


      $title = $tenant->name . ' | Log Stok Toko';

      return view(
         'pages.admin.produk-toko.log',
         compact('tenant', 'logStok', 'tanggalAktif', 'title')
      );
   }

   public function detailLogToko($produkVarianId, Request $request)
   {
      // =========================
      // VALIDASI TENANT
      // =========================
      $tenant = $request->tenant;

      if (! $tenant->isActive) {
         return redirect()->back()->with('error', 'Tenant tidak aktif.');
      }

      $title = $tenant->name . ' | Detail Log Stok Toko';

      // =========================
      // TANGGAL FILTER
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->toDateString()
         : now()->toDateString();

      // =========================
      // AMBIL VARIAN + PRODUK TOKO
      // =========================
      $produkVarian = DB::table('produk_varian')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->where('produk_varian.id', $produkVarianId)
         ->where('produk_toko.tenant_id', $tenant->id)
         ->select(
            'produk_varian.id as produk_varian_id',
            'produk_varian.nama as nama_varian',
            'produk_toko.id as produk_toko_id',
            'produk_toko.produk_id',
            'produk_toko.stok',
            'produk.nama_produk'
         )
         ->first();

      if (! $produkVarian) {
         return back()->with('error', 'Produk varian tidak ditemukan.');
      }

      // =========================
      // STOK MASUK TOKO (GLOBAL)
      // =========================
      $stokMasuk = DB::table('stok')
         ->where('tenant_id', $tenant->id)
         ->where('produk_id', $produkVarian->produk_id)
         ->whereDate('created_at', $tanggalAktif)
         ->select(
            'created_at',
            'stok_masuk as qty',
            DB::raw("'masuk' as tipe")
         );

      // =========================
      // STOK KELUAR (RETUR) PER VARIAN
      // =========================
      $stokKeluar = DB::table('stok_toko_keluar')
         ->where('tenant_id', $tenant->id)
         ->where('produk_varian_id', $produkVarianId)
         ->whereDate('created_at', $tanggalAktif)
         ->select(
            'created_at',
            'jumlah_retur as qty',
            DB::raw("'keluar' as tipe")
         );

      // =========================
      // PRODUK TERJUAL PER VARIAN
      // =========================
      $produkTerjualQuery = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->where('detail_transaksis.produk_varian_id', $produkVarianId)
         ->whereDate('transaksis.created_at', $tanggalAktif)
         ->select(
            'transaksis.created_at',
            'detail_transaksis.jumlah as qty',
            DB::raw("'terjual' as tipe")
         );

      $produkTerjual = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->where('detail_transaksis.produk_varian_id', $produkVarianId)
         ->whereDate('transaksis.created_at', $tanggalAktif)
         ->sum('detail_transaksis.jumlah');

      // =========================
      // GABUNGKAN LOG
      // =========================
      $logs = $stokMasuk
         ->unionAll($stokKeluar)
         ->unionAll($produkTerjualQuery)
         ->orderBy('created_at', 'asc')
         ->get();

      // =========================
      // HITUNG STOK BERJALAN
      // =========================
      $stokAkhir = $produkVarian->stok;

      $totalMasuk  = $logs->where('tipe', 'masuk')->sum('qty');
      $totalKeluar = $logs->where('tipe', 'keluar')->sum('qty');

      $stokAwal = $stokAkhir - $totalMasuk + $totalKeluar;
      $stokSaatIni = $stokAwal;

      $logs = $logs->map(function ($item) use (&$stokSaatIni) {

         $item->stok_masuk  = 0;
         $item->stok_keluar = 0;
         $item->terjual     = 0;

         if ($item->tipe === 'masuk') {
            $stokSaatIni += $item->qty;
            $item->stok_masuk = $item->qty;
         } elseif ($item->tipe === 'keluar') {
            $stokSaatIni -= $item->qty;
            $item->stok_keluar = $item->qty;
         } elseif ($item->tipe === 'terjual') {
            $stokSaatIni -= $item->qty;
            $item->terjual = $item->qty;
         }

         $item->stok_saat_itu = $stokSaatIni;
         $item->jam = Carbon::parse($item->created_at)->format('H:i:s');

         return $item;
      });

      return view('pages.admin.produk-toko.detail-log', [
         'tenant'        => $tenant,
         'title'         => $title,
         'produkVarian'  => $produkVarian,
         'logs'          => $logs,
         'tanggalAktif'  => $tanggalAktif,
         'stokAwal'      => $stokAwal,
         'produkTerjual' => $produkTerjual
      ]);
   }


   public function shift(Request $request)
   {

      $tenant = $request->tenant;
      // cek status tenant dan batasi agar tidak dapat masuk ke tanant yg tidak aktif
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }
      $shift = $tenant->shift()->get();
      $title = $tenant->name . ' | Shift';
      if (! $shift) {
         return redirect()->back()->with('error', 'Data shift tidak ditemukan.');
      } else {
         return view('pages.admin.shift.index', compact('tenant', 'shift', 'title'));
      }
   }

   public function storeShift(Request $request)
   {

      $activeTenant = session('active_tenant_id');

      $tenant = Tenant::findOrFail($activeTenant);
      if (! $tenant) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
      }

      $messages = [
         'shift.required' => 'Shift wajib diisi.',
         'shift.string' => 'Shift harus berupa string.',
         'shift.max' => 'Shift maksimal 30 karakter.',
         'shift.unique' => 'Shift sudah ada.',
         'start_time.required' => 'Waktu mulai wajib diisi.',
         'start_time.date_format' => 'Format waktu mulai tidak valid.',
         'end_time.required' => 'Waktu selesai wajib diisi.',
         'end_time.date_format' => 'Format waktu selesai tidak valid.',
      ];

      $validate = $request->validate([
         'shift' => [
            'required',
            'string',
            'max:30',
            Rule::unique('shift', 'shift')
               ->where('tenant_id', $tenant->id)
         ],
         'start_time' => ['required', 'date_format:H:i:s'],
         'end_time' => ['required', 'date_format:H:i:s']
      ], $messages);

      $create = $tenant->shift()->create([
         'tenant_id' => $tenant->id,
         'shift' => $validate['shift'],
         'start_time' => $validate['start_time'],
         'end_time' => $validate['end_time'],
      ]);
      if (! $create) {
         return redirect()->back()->with('error', 'Gagal menambahkan shift.');
      } else {
         return redirect()->route('admin.shift', $tenant->id)->with('success', 'Shift berhasil ditambahkan.');
      }
   }

   public function updateShift(Request $request, $id)
   {
      $activeTenant = session('active_tenant_id');

      if (! $activeTenant) {
         return redirect()->back()->with('error', 'Tenant aktif tidak ditemukan.');
      }

      $messages = [
         'shift.required' => 'Shift wajib diisi.',
         'shift.string' => 'Shift harus berupa string.',
         'shift.max' => 'Shift maksimal 30 karakter.',
         'shift.unique' => 'Shift sudah ada.',
         'start_time.required' => 'Waktu mulai wajib diisi.',
         'start_time.date_format' => 'Format waktu mulai tidak valid.',
         'end_time.required' => 'Waktu selesai wajib diisi.',
         'end_time.date_format' => 'Format waktu selesai tidak valid.',
      ];

      $validate = $request->validate([
         'shift' => [
            'required',
            'string',
            'max:30',
            Rule::unique('shift', 'shift')
               ->ignore($id)
               ->where(
                  function ($query) use ($activeTenant) {
                     $query->where('tenant_id', $activeTenant);
                  }
               ),
         ],
         'start_time' => ['required', 'date_format:H:i:s'],
         'end_time' => ['required', 'date_format:H:i:s']
      ], $messages);

      $shift = Shift::findOrFail($id);
      if (! $shift) {
         return redirect()->back()->with('error', 'Shift tidak ditemukan.');
      }
      $update = $shift->update([
         'shift' => $validate['shift'],
         'start_time' => $validate['start_time'],
         'end_time' => $validate['end_time'],
      ]);

      if (! $update) {
         return redirect()->back()->with('error', 'Gagal mengupdate shift.');
      } else {
         return redirect()->route('admin.shift', $shift->tenant_id)->with('success', 'Shift berhasil diupdate.');
      }
   }

   public function deleteShift($id)
   {
      $shift = Shift::findOrFail($id);
      if (! $shift) {
         return redirect()->back()->with('error', 'Shift tidak ditemukan.');
      }
      $delete = $shift->delete();

      if (! $delete) {
         return redirect()->back()->with('error', 'Gagal menghapus shift.');
      } else {
         return redirect()->back()->with('success', 'Shift berhasil dihapus.');
      }
   }

   public function kasir(Request $request)
   {
      $tenant = $request->tenant;
      // cek status tenant dan batasi agar tidak dapat masuk ke tanant yg tidak aktif
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      // Ambil semua user role "kasir" yg ada di tenant ini
      $kasir = User::where('role', 'kasir')
         ->whereHas('tenants', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
         })
         ->get();


      $title = $tenant->name . ' | Data Kasir';
      // dd($kasir);
      return view('pages.admin.kasir.index', compact('kasir', 'tenant', 'title'));
   }

   public function storeKasir(Request $request)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('choose.tenant')->with('error', 'Silakan pilih tenant dulu');
      }

      $messages = [
         'name.required' => 'Nama kasir wajib diisi.',
         'name.max' => 'Nama kasir maksimal 25 karakter.',
         'username.required' => 'Username wajib diisi.',
         'username.min' => 'Username minimal 4 karakter.',
         'username.max' => 'Username maksimal 20 karakter.',
         'username.unique' => 'Username sudah digunakan.',
         'email.required' => 'Email wajib diisi.',
         'email.email' => 'Format email tidak valid.',
         'email.unique' => 'Email sudah digunakan.',
         'password.required' => 'Password wajib diisi.',
         'password.min' => 'Password minimal 6 karakter.',
      ];

      $validate = $request->validate([
         'name' => 'required|string|max:25',
         'username' =>
         [
            'required',
            'string',
            'min:4',
            'max:20',
            // Username unique
            Rule::unique('users', 'username')

         ],
         'email' =>
         [
            'required',
            'email',
            // UNIQUE EMAIL 
            Rule::unique('users', 'email')

         ],
         'password' => 'required|string|min:6',
      ], $messages);

      // Buat user baru dengan role kasir
      $kasir = User::create([
         'name' => $validate['name'],
         'username' => $validate['username'],
         'email' => $validate['email'],
         'password' => bcrypt($validate['password']),
         'isActive' => true,
         'role' => 'kasir',
      ]);

      if (! $kasir) {
         return redirect()->back()->with('error', 'Gagal menambahkan kasir.');
      }

      // Hubungkan user kasir dengan tenant
      $kasir->tenants()->attach($tenantId);

      return redirect()->route('admin.kasir', $tenantId)->with('success', 'Kasir berhasil ditambahkan.');
   }

   public function updateKasir(Request $request, $id)
   {
      $tenantId = session('active_tenant_id');
      if (!$tenantId) {
         return redirect()->route('choose.tenant')->with('error', 'Silakan pilih tenant dulu');
      }

      $messages = [
         'name.required' => 'Nama kasir wajib diisi.',
         'name.max' => 'Nama kasir maksimal 25 karakter.',
         'username.required' => 'Username wajib diisi.',
         'username.min' => 'Username minimal 4 karakter.',
         'username.max' => 'Username maksimal 20 karakter.',
         'username.unique' => 'Username sudah digunakan.',
         'email.required' => 'Email wajib diisi.',
         'email.email' => 'Format email tidak valid.',
         'email.unique' => 'Email sudah digunakan.',
         'password.min' => 'Password minimal 6 karakter.',
      ];

      $validate = $request->validate([
         'name' => 'required|string|max:25',
         'username' => [
            'required',
            'string',
            'min:4',
            'max:20',
            Rule::unique('users', 'username')
               ->ignore($id)

         ],
         'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')
               ->ignore($id)
         ],
         'password' => 'nullable|string|min:6', // <–– tidak wajib
      ], $messages);

      $kasir = User::findOrFail($id);

      $isActive = $request->has('isActive') ? '1' : '0';

      // Build data update
      $data = [
         'name' => $validate['name'],
         'username' => $validate['username'],
         'email' => $validate['email'],
         'isActive' => $isActive,
      ];

      // Update password hanya jika diisi
      if (!empty($request->password)) {
         $data['password'] = bcrypt($request->password);
      }

      $update = $kasir->update($data);

      if (! $update) {
         return redirect()->back()->with('error', 'Gagal memperbarui kasir.');
      } else {
         return redirect()->back()->with('success', 'Kasir berhasil diperbarui.');
      }
   }

   public function deleteKasir($id)
   {
      $kasir = User::findOrFail($id);
      if (! $kasir) {
         return redirect()->back()->with('error', 'Kasir tidak ditemukan.');
      }
      $kasir->isActive = ! $kasir->isActive;
      $kasir->save();

      $message = $kasir->isActive
         ? 'Kasir berhasil diaktifkan kembali.'
         : 'Kasir berhasil dinonaktifkan.';

      return back()->with('success', $message);
   }

   public function pengeluaran(Request $request)
   {
      $tenant = $request->tenant;

      // =========================
      // VALIDASI TENANT
      // =========================
      if (! $tenant || ! $tenant->isActive) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // =========================
      // FILTER TANGGAL
      // =========================
      $searchDate = $request->search_date; // format: YYYY-MM-DD

      if ($searchDate) {
         $startDate = \Carbon\Carbon::parse($searchDate)->startOfDay();
         $endDate   = \Carbon\Carbon::parse($searchDate)->endOfDay();
      } else {
         // default: hari ini
         $startDate = \Carbon\Carbon::today()->startOfDay();
         $endDate   = \Carbon\Carbon::today()->endOfDay();
      }

      // =========================
      // BASE QUERY (FILTER TANGGAL)
      // =========================
      $baseQuery = $tenant->pengeluaran()
         ->whereBetween('created_at', [$startDate, $endDate]);

      // =========================
      // DATA LIST
      // =========================
      $pengeluaran = (clone $baseQuery)
         ->orderBy('created_at', 'desc')
         ->get();

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

      $title = $tenant->name . ' | Pengeluaran';

      return view('pages.admin.pengeluaran.index', [
         'pengeluaran'        => $pengeluaran,
         'tenant'             => $tenant,
         'title'              => $title,
         'pengeluaran_kasir'  => $pengeluaran_kasir,
         'pengeluaran_admin'  => $pengeluaran_admin,
         'total_pengeluaran'  => $total_pengeluaran,
         'searchDate'        => $searchDate ?? now()->format('Y-m'),
      ]);
   }

   public function addPengeluaran(Request $request)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $tenant = Tenant::findOrFail($tenantId);
      $title = $tenant->name . ' | Tambah Pengeluaran';
      return view('pages.admin.pengeluaran.add', compact('tenant', 'title'));
   }

   public function storePengeluaran(Request $request)
   {
      // dd($request->all(), $id);
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

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
         'created_at' => Carbon::now()
      ]);

      if (! $create) {
         return redirect()->route('admin.pengeluaran')->with('error', 'Gagal menambahkan pengeluaran.');
      }

      return redirect()->route('admin.pengeluaran')->with('success', 'Pengeluaran berhasil ditambahkan.');
   }

   public function editPengeluaran($id)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $tenant = Tenant::findOrFail($tenantId);
      $pengeluaran = $tenant->pengeluaran()->where('id', $id)->firstOrFail();
      $title = $tenant->name . ' | Edit Pengeluaran';
      return view('pages.admin.pengeluaran.edit', compact('pengeluaran', 'tenant', 'title'));
   }

   public function updatePengeluaran(Request $request, $id)
   {
      $tenantId = session('active_tenant_id');
      // dd($request->all(), $id, $tenantId);

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

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

      if ($pengeluaran->created_at->addDays(3)->isPast()) {
         return redirect()->route('admin.pengeluaran')->with('error', 'Data stok tidak bisa diubah karena sudah lebih dari 3 hari');
      }

      $update = $pengeluaran->update([
         'deskripsi' => $validate['deskripsi'],
         'jumlah' => $jumlah,
         'updated_at' => Carbon::now()
      ]);

      if (! $update) {
         return redirect()->route('admin.pengeluaran')->with('error', 'Gagal memperbarui pengeluaran.');
      }

      return redirect()->route('admin.pengeluaran')->with('success', 'Pengeluaran berhasil diperbarui.');
   }

   public function deletePengeluaran($id)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $tenant = Tenant::findOrFail($tenantId);

      if (! $tenant) {
         return redirect()->back()->with('error', 'Tenant tidak ditemukan.');
      }

      $pengeluaran = $tenant->pengeluaran()->where('id', $id)->firstOrFail();

      if ($pengeluaran->created_at->addDays(3)->isPast()) {
         return redirect()->route('admin.pengeluaran')->with('error', 'Data stok tidak bisa dihapus karena sudah lebih dari 3 hari');
      }

      $delete = $pengeluaran->delete();

      if (! $delete) {
         return redirect()->route('admin.pengeluaran')->with('error', 'Gagal menghapus pengeluaran.');
      }

      return redirect()->route('admin.pengeluaran')->with('success', 'Pengeluaran berhasil dihapus.');
   }

   public function pendapatan(Request $request)
   {
      $tenant = $request->tenant;

      // =========================
      // VALIDASI TENANT
      // =========================
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      $title = $tenant->name . ' | Pendapatan';

      // =========================
      // DEFAULT TANGGAL BERDASARKAN JAM KERJA
      // =========================
      $now = Carbon::now();

      // Jika sebelum jam 06.00 → business day masih kemarin
      $defaultDate = $now->hour < 6
         ? $now->copy()->subDay()->toDateString()
         : $now->toDateString();

      // =========================
      // FILTER TANGGAL (DATE)
      // =========================
      $startDate = $request->filled('start_date')
         ? Carbon::parse($request->start_date)->startOfDay()
         : Carbon::parse($defaultDate)->startOfDay();

      $endDate = $request->filled('end_date')
         ? Carbon::parse($request->end_date)->endOfDay()
         : Carbon::parse($defaultDate)->endOfDay();

      // Validasi
      if ($startDate->gt($endDate)) {
         return back()
            ->withInput()
            ->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
      }

      // =========================
      // SHIFT RANGE (06.00 - 05.59)
      // =========================
      $startDateShift = $startDate->copy()->addHours(6);
      $endDateShift   = $endDate->copy()->addHours(6);

      // =========================
      // QUERY LAPORAN HARIAN
      // =========================
      $laporan = DB::query()
         ->fromSub(function ($query) use ($tenant, $startDateShift, $endDateShift) {

            /*
         |--------------------------------------------------
         | PENJUALAN
         |--------------------------------------------------
         */
            $query->selectRaw('
            DATE(DATE_SUB(transaksis.created_at, INTERVAL 6 HOUR)) AS tanggal,
            SUM(detail_transaksis.jumlah) AS jumlah_produk_terjual,
            SUM(detail_transaksis.total_harga_produk) AS total_harga_penjualan_produk,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "online"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS penjualan_online,

            SUM(CASE 
               WHEN transaksis.metode_pembayaran = "offline"
               THEN detail_transaksis.total_harga_produk
               ELSE 0
            END) AS penjualan_offline,

            0 AS total_produk_keluar,
            0 AS total_jumlah_retur,
            0 AS total_pengeluaran,
            0 AS pengeluaran_toko
         ')
               ->from('detail_transaksis')
               ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
               ->where('transaksis.tenant_id', $tenant->id)
               ->whereBetween('transaksis.created_at', [$startDateShift, $endDateShift])
               ->groupByRaw('DATE(DATE_SUB(transaksis.created_at, INTERVAL 6 HOUR))')

               ->unionAll(

                  /*
            |--------------------------------------------------
            | STOK TOKO KELUAR / RETUR
            |--------------------------------------------------
            */
                  DB::table('stok_toko_keluar')
                     ->selectRaw('
                  DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) AS tanggal,
                  0, 0, 0, 0,
                  SUM(jumlah_retur * harga_produk),
                  SUM(jumlah_retur),
                  0, 0
               ')
                     ->where('tenant_id', $tenant->id)
                     ->whereBetween('created_at', [$startDateShift, $endDateShift])
                     ->groupByRaw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))')
               )

               ->unionAll(

                  /*
            |--------------------------------------------------
            | PENGELUARAN
            |--------------------------------------------------
            */
                  DB::table('pengeluaran')
                     ->selectRaw('
                  DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) AS tanggal,
                  0, 0, 0, 0, 0, 0,
                  SUM(jumlah),
                  SUM(jumlah)
               ')
                     ->where('tenant_id', $tenant->id)
                     ->whereBetween('created_at', [$startDateShift, $endDateShift])
                     ->groupByRaw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))')
               );
         }, 'laporan_harian')

         /*
      |--------------------------------------------------
      | AGREGASI FINAL
      |--------------------------------------------------
      */
         ->selectRaw('
         tanggal,
         SUM(jumlah_produk_terjual) AS jumlah_produk_terjual,
         SUM(total_harga_penjualan_produk) AS total_harga_penjualan_produk,
         SUM(penjualan_online) AS penjualan_online,
         SUM(penjualan_offline) AS penjualan_offline,
         SUM(total_produk_keluar) AS total_produk_keluar,
         SUM(total_jumlah_retur) AS total_jumlah_retur,
         SUM(total_pengeluaran) AS total_pengeluaran,
         SUM(pengeluaran_toko) AS pengeluaran_toko,
         (
            SUM(total_harga_penjualan_produk)
            - SUM(total_produk_keluar)
            - SUM(total_pengeluaran)
         ) AS laba_bersih
      ')
         ->groupBy('tanggal')
         ->orderBy('tanggal')
         ->get();

      // =========================
      // TOTAL RENTANG TANGGAL
      // =========================
      $totalBulanan = [
         'jumlah_produk_terjual' => $laporan->sum('jumlah_produk_terjual'),
         'total_penjualan'       => $laporan->sum('total_harga_penjualan_produk'),
         'penjualan_online'      => $laporan->sum('penjualan_online'),
         'penjualan_offline'     => $laporan->sum('penjualan_offline'),
         'total_produk_keluar'   => $laporan->sum('total_produk_keluar'),
         'total_jumlah_retur'    => $laporan->sum('total_jumlah_retur'),
         'pengeluaran_toko'      => $laporan->sum('pengeluaran_toko'),
         'total_pengeluaran'     => $laporan->sum('total_produk_keluar')
            + $laporan->sum('pengeluaran_toko'),
         'laba_bersih'           => $laporan->sum('laba_bersih'),
      ];

      return view('pages.admin.pendapatan.index', [
         'laporan'      => $laporan,
         'totalBulanan' => $totalBulanan,
         'startDate'    => $startDate->format('Y-m-d'),
         'endDate'      => $endDate->format('Y-m-d'),
         'tenant'       => $tenant,
         'title'        => $title
      ]);
   }

   public function exportPendapatan(Request $request)
   {
      $tenant = $request->tenant;

      if (! $tenant || ! $tenant->isActive) {
         return redirect()->back()->with('error', 'Tenant tidak valid.');
      }

      // ===============================
      // FILTER TANGGAL KERJA (SHIFT)
      // ===============================
      if ($request->filled('start_date')) {
         $startDate = Carbon::parse($request->start_date);
      } else {
         $startDate = Carbon::today();
      }

      if ($request->filled('end_date')) {
         $endDate = Carbon::parse($request->end_date);
      } else {
         $endDate = $startDate;
      }

      // ===============================
      // SHIFT RANGE (06.00 - 05.59)
      // ===============================
      $startDateShift = $startDate->copy()->setTime(6, 0, 0);
      $endDateShift   = $endDate->copy()->addDay()->setTime(5, 59, 59);

      // dd($startDateShift, $endDateShift);

      // ===============================
      // VALIDASI
      // ===============================
      if ($startDate->gt($endDate)) {
         return redirect()->back()->withErrors([
            'tanggal' => 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir'
         ]);
      }


      // ===============================
      // AMBIL DATA LAPORAN (SAMA PERSIS)
      // ===============================
      $laporan = DB::query()
         ->fromSub(function ($query) use ($startDateShift, $endDateShift, $tenant) {

            $query->selectRaw('
            DATE(DATE_SUB(transaksis.created_at, INTERVAL 6 HOUR)) AS tanggal,
            SUM(detail_transaksis.jumlah) AS jumlah_produk_terjual,
            SUM(detail_transaksis.total_harga_produk) AS total_harga_penjualan_produk,

            SUM(CASE WHEN transaksis.metode_pembayaran = "online"
               THEN detail_transaksis.total_harga_produk ELSE 0 END) AS penjualan_online,

            SUM(CASE WHEN transaksis.metode_pembayaran = "offline"
               THEN detail_transaksis.total_harga_produk ELSE 0 END) AS penjualan_offline,

            0 AS total_produk_keluar,
            0 AS total_jumlah_retur,
            0 AS total_pengeluaran,
            0 AS pengeluaran_toko
         ')
               ->from('detail_transaksis')
               ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
               ->where('transaksis.tenant_id', $tenant->id)
               ->whereBetween('transaksis.created_at', [$startDateShift, $endDateShift])
               ->groupByRaw('DATE(DATE_SUB(transaksis.created_at, INTERVAL 6 HOUR))')

               ->unionAll(
                  DB::table('stok_toko_keluar')
                     ->selectRaw('
                  DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) AS tanggal,
                  0,0,0,0,
                  SUM(jumlah_retur * harga_produk),
                  SUM(jumlah_retur),
                  0,0
               ')
                     ->where('tenant_id', $tenant->id)
                     ->whereBetween('created_at', [$startDateShift, $endDateShift])
                     ->groupByRaw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))')
               )

               ->unionAll(
                  DB::table('pengeluaran')
                     ->selectRaw('
                  DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) AS tanggal,
                  0,0,0,0,0,0,
                  SUM(jumlah),
                  SUM(jumlah)
               ')
                     ->where('tenant_id', $tenant->id)
                     ->whereBetween('created_at', [$startDateShift, $endDateShift])
                     ->groupByRaw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))')
               );
         }, 'laporan_harian')

         ->selectRaw('
         tanggal,
         SUM(jumlah_produk_terjual) AS jumlah_produk_terjual,
         SUM(total_harga_penjualan_produk) AS total_harga_penjualan_produk,
         SUM(penjualan_online) AS penjualan_online,
         SUM(penjualan_offline) AS penjualan_offline,
         SUM(total_produk_keluar) AS total_produk_keluar,
         SUM(total_jumlah_retur) AS total_jumlah_retur,
         SUM(total_pengeluaran) AS total_pengeluaran,
         SUM(pengeluaran_toko) AS pengeluaran_toko,
         (
            SUM(total_harga_penjualan_produk)
            - SUM(total_produk_keluar)
            - SUM(total_pengeluaran)
         ) AS laba_bersih
      ')
         ->groupBy('tanggal')
         ->orderBy('tanggal')
         ->get();

      // ===============================
      // REKAP TOTAL (SAMA VIEW)
      // ===============================
      $rekap = [
         'produk_terjual'     => $laporan->sum('jumlah_produk_terjual'),
         'hasil_penjualan'    => $laporan->sum('total_harga_penjualan_produk'),
         'qris'               => $laporan->sum('penjualan_online'),
         'tunai'              => $laporan->sum('penjualan_offline'),
         'produk_keluar'      => $laporan->sum('total_jumlah_retur'),
         'harga_produk_keluar' => $laporan->sum('total_produk_keluar'),
         'pengeluaran_toko'   => $laporan->sum('pengeluaran_toko'),
         'total_pengeluaran'  => $laporan->sum('total_produk_keluar') + $laporan->sum('pengeluaran_toko'),
         'laba_bersih'        => $laporan->sum('laba_bersih'),
      ];

      // ===============================
      // BUAT EXCEL
      // ===============================
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      $row = 1;

      // JUDUL
      $sheet->setCellValue("A{$row}", "LAPORAN PENDAPATAN - {$tenant->name} TANGGAL {$startDate->format('d-m-Y')} s/d {$endDate->format('d-m-Y')}");
      $sheet->mergeCells("A{$row}:I{$row}");
      $row += 2;

      // HEADER
      $sheet->fromArray([
         'Tanggal',
         'Produk Terjual',
         'Penjualan',
         'QRIS',
         'Tunai',
         'Produk Keluar',
         'Harga Produk Keluar',
         'Pengeluaran Toko',
         'Laba Bersih'
      ], null, "A{$row}");

      $row++;

      // DATA
      foreach ($laporan as $d) {
         $sheet->fromArray([
            $d->tanggal,
            $d->jumlah_produk_terjual,
            'Rp' . number_format($d->total_harga_penjualan_produk, 0, ',', '.'),
            'Rp' . number_format($d->penjualan_online, 0, ',', '.'),
            'Rp' . number_format($d->penjualan_offline, 0, ',', '.'),
            $d->total_jumlah_retur,
            'Rp' . number_format($d->total_produk_keluar, 0, ',', '.'),
            'Rp' . number_format($d->pengeluaran_toko, 0, ',', '.'),
            'Rp' . number_format($d->laba_bersih, 0, ',', '.'),
         ], null, "A{$row}");
         $row++;
      }

      $startTableRow = 3; // baris header tabel
      $endTableRow   = $row - 1; // baris terakhir data

      $sheet->getStyle("A{$startTableRow}:I{$endTableRow}")
         ->applyFromArray([
            'borders' => [
               'allBorders' => [
                  'borderStyle' => Border::BORDER_THIN,
                  'color' => ['argb' => 'FF000000'],
               ],
            ],
         ]);

      $sheet->getStyle("A{$startTableRow}:I{$startTableRow}")
         ->getFont()
         ->setBold(true);

      // ===============================
      // REKAP DI BAWAH TABEL
      // ===============================
      $row += 2;

      $rekapRows = [
         ['Produk Terjual', $rekap['produk_terjual'] . ' item'],
         ['Hasil Penjualan', 'Rp' . number_format($rekap['hasil_penjualan'], 0, ',', '.')],
         ['Pembayaran QRIS', 'Rp' . number_format($rekap['qris'], 0, ',', '.')],
         ['Pembayaran Tunai', 'Rp' . number_format($rekap['tunai'], 0, ',', '.')],
         ['Pengeluaran Toko', 'Rp' . number_format($rekap['pengeluaran_toko'], 0, ',', '.')],
         ['Laba Tunai', 'Rp' . number_format($rekap['tunai'] - $rekap['pengeluaran_toko'], 0, ',', '.')],
         ['Produk Keluar', $rekap['produk_keluar'] . ' item'],
         ['Harga Produk Keluar', 'Rp' . number_format($rekap['harga_produk_keluar'], 0, ',', '.')],
         ['Laba Bersih', 'Rp' . number_format($rekap['laba_bersih'], 0, ',', '.')],
      ];

      foreach ($rekapRows as $r) {
         $sheet->setCellValue("A{$row}", $r[0]);
         $sheet->setCellValue("B{$row}", $r[1]);
         $row++;
      }

      // AUTO WIDTH
      foreach (range('A', 'I') as $col) {
         $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // ===============================
      // DOWNLOAD
      // ===============================
      $filename = 'pendapatan-'
         . Str::slug($tenant->name)
         . '-'
         . $startDate->format('d-m-Y')
         . '-'
         . $endDate->format('d-m-Y')
         . '.xlsx';

      header('Content-Type: application/vnd.ms-excel');
      header("Content-Disposition: attachment;filename=\"$filename\"");
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
   }

   public function dataTransaksi(Request $request)
   {
      // =========================
      // 1️⃣ TENANT DARI MIDDLEWARE
      // =========================
      $tenant = $request->tenant;

      // =========================
      // FILTER RENTANG TANGGAL
      // =========================
      $now = Carbon::now();

      if ($request->filled('start_date') && $request->filled('end_date')) {

         // ✅ USER INPUT → PAKAI APA ADANYA
         $startDate = Carbon::parse($request->start_date);
         $endDate   = Carbon::parse($request->end_date);
      } else {

         // 🔥 DEFAULT HARI OPERASIONAL
         if ($now->hour < 6) {
            // sebelum jam 6 → hari kerja kemarin
            $startDate = $now->copy()->subDay()->setTime(6, 0, 0);
            $endDate   = $now->copy()->setTime(5, 59, 59);
         } else {
            // setelah jam 6 → hari kerja hari ini
            $startDate = $now->copy()->setTime(6, 0, 0);
            $endDate   = $now->copy()->addDay()->setTime(5, 59, 59);
         }
      }


      // ✅ VALIDASI RANGE TANGGAL
      if ($startDate->gt($endDate)) {
         return back()
            ->withInput()
            ->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
      }

      // =========================
      // FILTER METODE PEMBAYARAN
      // =========================
      $metodePembayaran = $request->filled('metode_pembayaran')
         ? $request->metode_pembayaran
         : 'all';

      // =========================
      // 3️⃣ HISTORI TRANSAKSI
      // =========================
      $transaksi = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->select(
            'transaksis.id as id_transaksi',
            'transaksis.*',
            'users.name as nama_kasir'
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startDate, $endDate])
         ->when($metodePembayaran !== 'all', function ($q) use ($metodePembayaran) {
            $q->where('transaksis.metode_pembayaran', $metodePembayaran);
         })
         ->orderByDesc('transaksis.created_at')
         ->get();



      $totalTransaksi = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startDate, $endDate])
         ->when($metodePembayaran !== 'all', function ($q) use ($metodePembayaran) {
            $q->where('transaksis.metode_pembayaran', $metodePembayaran);
         })
         ->selectRaw('
      SUM(detail_transaksis.jumlah) AS total_item_terjual,
      SUM(detail_transaksis.total_harga_produk) AS total_hasil_penjualan,

      SUM(
         CASE 
            WHEN transaksis.metode_pembayaran = "online"
            THEN detail_transaksis.total_harga_produk
            ELSE 0
         END
      ) AS total_penjualan_online,

      SUM(
         CASE 
            WHEN transaksis.metode_pembayaran = "offline"
            THEN detail_transaksis.total_harga_produk
            ELSE 0
         END
      ) AS total_penjualan_offline
      ')
         ->first();




      // =========================
      // 4️⃣ PRODUK TERLARIS
      // =========================
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->select(
            'produk.id as produk_id',
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            DB::raw('SUM(detail_transaksis.jumlah) as total_terjual'),
            DB::raw('SUM(detail_transaksis.total_harga_produk) as total_penjualan')
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startDate, $endDate])
         ->groupBy(
            'produk.id',
            'produk.nama_produk',
            'produk_varian.nama'
         )
         ->orderByDesc('total_terjual')
         ->get();
      // dd($produkTerlaris);

      $validasi_retur = Validasi::join('detail_transaksis', 'detail_transaksis.id', '=', 'validasi.detail_transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->select(
            'validasi.*',
            'produk_varian.nama as nama_varian',
            'produk_varian.harga_jual'
         )->where('produk_toko.tenant_id', $tenant->id)->where('status_validasi', 'pending')
         ->get();

      $dataRetur = DB::table('validasi')
         ->join('detail_transaksis', 'detail_transaksis.id', '=', 'validasi.detail_transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->join('transaksis', 'transaksis.id', '=', 'validasi.transaksi_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('validasi.created_at', [$startDate, $endDate])
         ->select(
            'validasi.*',
            'transaksis.id as transaksi_id',
            'transaksis.kode_transaksi as kode',
            'produk_varian.nama as nama_varian',
            'detail_transaksis.harga_satuan as harga_produk',
         )
         ->orderByDesc('validasi.created_at')
         ->get();

      // dd($dataRetur);

      // =========================
      // 5️⃣ RETURN VIEW
      // =========================
      return view('pages.admin.data-transaksi.index', [
         'title'         => $tenant->name . ' | Data Transaksi',
         'tenant'        => $tenant,
         'transaksi'     => $transaksi,
         'totalTransaksi' => $totalTransaksi,
         'produkTerlaris' => $produkTerlaris,
         'startDate'     => $startDate->format('Y-m-d\TH:i'),
         'endDate'       => $endDate->format('Y-m-d\TH:i'),
         'validasi_retur' => $validasi_retur,
         'dataRetur'     => $dataRetur,
      ]);
   }

   public function detailTransaksi($id)
   {
      $tenantId = session('active_tenant_id');

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
         )
         ->where('validasi.transaksi_id', $transaksi->id)
         ->get();
      $data = [
         'title' => 'Detail Transaksi : ' . $transaksi->kode_transaksi,
         'transaksi' => $transaksi,
         'tanggal_transaksi' => $tanggal_transaksi,
         'kasir' => $kasir,
         'detail_transaksi' => $detailTransaksi,
         'tenant' => $tenant,
         'validasi_retur' => $validasi_retur
      ];

      return view('pages.admin.data-transaksi.detail', $data);
   }

   public function validasi(Request $request, $id)
   {
      $request->validate([
         'status_validasi' => 'required|in:diterima,ditolak',
      ]);

      $tenantId = session('active_tenant_id');
      if (! $tenantId) {
         return redirect()->route('login')
            ->withErrors(['tenant' => 'Tenant belum dipilih.']);
      }

      $tenant = Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->firstOrFail();

      DB::transaction(function () use ($request, $id, $tenant) {

         // =========================
         // 1️⃣ DATA VALIDASI
         // =========================
         $validasi = Validasi::lockForUpdate()->findOrFail($id);

         // kalau sudah diproses → stop
         if ($validasi->status_validasi !== 'pending') {
            throw new \Exception('Request ini sudah diproses.');
         }

         // update status validasi
         $validasi->update([
            'admin_id'        => Auth::id(),
            'status_validasi' => $request->status_validasi,
         ]);

         // =========================
         // 2️⃣ JIKA DITOLAK → STOP
         // =========================
         if ($request->status_validasi === 'ditolak') {
            return;
         }

         // =========================
         // 3️⃣ DATA TERKAIT
         // =========================
         $detail = DetailTransaksi::lockForUpdate()
            ->findOrFail($validasi->detail_transaksi_id);

         $transaksi = Transaksi::lockForUpdate()
            ->findOrFail($validasi->transaksi_id);

         $jumlahRetur = $validasi->jumlah_item;
         $hargaItem   = $validasi->harga_item;


         // hitung poin sebelum perubahan
         $poinSebelum = intdiv($transaksi->total_harga, 10000);

         // dd($poinSebelum);

         // =========================
         // 4️⃣ UPDATE DETAIL TRANSAKSI
         // =========================
         $jumlahBaru = $detail->jumlah - $jumlahRetur;

         if ($jumlahBaru < 0) {
            throw new \Exception('Jumlah item tidak valid.');
         }

         $detail->update([
            'jumlah' => $jumlahBaru,
            'total_harga_produk' => $jumlahBaru * $hargaItem,
         ]);

         // =========================
         // 5️⃣ UPDATE TOTAL TRANSAKSI
         // =========================
         $totalItem = DetailTransaksi::where('transaksi_id', $transaksi->id)
            ->sum('jumlah');

         $totalHarga = DetailTransaksi::where('transaksi_id', $transaksi->id)
            ->sum('total_harga_produk');

         $transaksi->update([
            'total_item'   => $totalItem,
            'total_harga'  => $totalHarga,
            'bayar'        => $totalHarga,
            'kembalian'    => 0,
         ]);

         // =========================
         // 6️⃣ PROSES STOK
         // =========================
         $produkVarian = ProdukVarian::findOrFail($detail->produk_varian_id);
         $produkToko   = ProdukToko::lockForUpdate()
            ->findOrFail($produkVarian->produk_toko_id);

         // 🔹 PEMBATALAN → STOK KEMBALI
         if ($validasi->type === 'pembatalan') {
            $produkToko->increment('stok', $jumlahRetur);
         }

         // 🔹 RETUR → STOK TETAP KELUAR
         if ($validasi->type === 'retur') {
            StokTokoKeluar::create([
               'tenant_id'        => $tenant->id,
               'produk_varian_id' => $detail->produk_varian_id,
               'kasir_id'         => $transaksi->kasir_id,
               'jumlah_retur'     => $jumlahRetur,
               'harga_produk'     => $detail->harga_satuan,
               'keterangan'       => 'Retur transaksi ' . $transaksi->kode_transaksi,
            ]);
         }



         // =========================
         // 5️⃣A UPDATE POIN PELANGGAN
         // =========================
         if ($transaksi->pelanggan_id) {

            $poinSesudah = floor($totalHarga / 10000);
            $selisihPoin = $poinSesudah - $poinSebelum;

            if ($selisihPoin != 0) {
               Pelanggan::where('id', $transaksi->pelanggan_id)
                  ->lockForUpdate()
                  ->increment('poin', $selisihPoin);
            }
         }
      });

      return back()->with('success', 'Request berhasil diproses.');
   }

   public function exportDataTransaksi(Request $request)
   {
      $tenant = $request->tenant;

      // =========================
      // FILTER RENTANG TANGGAL (SAMA PERSIS)
      // =========================
      $now = Carbon::now();

      if ($request->filled('start_date') && $request->filled('end_date')) {

         // ✅ USER INPUT → PAKAI APA ADANYA
         $startDate = Carbon::parse($request->start_date);
         $endDate   = Carbon::parse($request->end_date);
      } else {

         // 🔥 DEFAULT HARI OPERASIONAL
         if ($now->hour < 6) {
            $startDate = $now->copy()->subDay()->setTime(6, 0, 0);
            $endDate   = $now->copy()->setTime(5, 59, 59);
         } else {
            $startDate = $now->copy()->setTime(6, 0, 0);
            $endDate   = $now->copy()->addDay()->setTime(5, 59, 59);
         }
      }

      // ===============================
      // VALIDASI
      // ===============================
      if ($startDate->gt($endDate)) {
         return redirect()->back()->withErrors([
            'tanggal' => 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir'
         ]);
      }


      /*
      |--------------------------------------------------------------------------
      | DATA TRANSAKSI
      |--------------------------------------------------------------------------
      */
      $transaksi = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startDate, $endDate])
         ->select(
            'transaksis.created_at',
            'transaksis.kode_transaksi',
            'transaksis.*',
            'users.name as kasir',
            'transaksis.metode_pembayaran',
            'transaksis.total_harga'
         )
         ->orderBy('transaksis.created_at')
         ->get();

      /*
      |--------------------------------------------------------------------------
      | REKAP TRANSAKSI
      |--------------------------------------------------------------------------
      */
      $rekap = [
         'total_item' => $transaksi->sum('total_item'),

         'total_penjualan' => $transaksi->sum('total_harga'),

         'online' => $transaksi
            ->where('metode_pembayaran', 'online')
            ->sum('total_harga'),

         'offline' => $transaksi
            ->where('metode_pembayaran', 'offline')
            ->sum('total_harga'),
      ];

      /*
      |--------------------------------------------------------------------------
      | PRODUK TERLARIS
      |--------------------------------------------------------------------------
      */
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->select(
            'produk.id as produk_id',
            'produk.nama_produk',
            'produk_varian.nama as nama_varian',
            DB::raw('SUM(detail_transaksis.jumlah) as total_terjual'),
            DB::raw('SUM(detail_transaksis.total_harga_produk) as total_penjualan')
         )
         ->where('transaksis.tenant_id', $tenant->id)
         ->whereBetween('transaksis.created_at', [$startDate, $endDate])
         ->groupBy(
            'produk.id',
            'produk.nama_produk',
            'produk_varian.nama'
         )
         ->orderByDesc('total_terjual')
         ->get();

      /*
      |--------------------------------------------------------------------------
      | BUAT EXCEL
      |--------------------------------------------------------------------------
      */
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      $row = 1;

      // ===============================
      // JUDUL
      // ===============================
      $sheet->setCellValue(
         "A{$row}",
         "DATA TRANSAKSI - {$tenant->name} 
         TANGGAL {$startDate->format('d-m-Y')} s/d {$endDate->format('d-m-Y')}
         (Jam Kerja 06.00 - 05.59)"
      );
      $sheet->mergeCells("A{$row}:G{$row}");
      $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
      $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $row += 2;

      // ===============================
      // HEADER TRANSAKSI
      // ===============================
      $headers = ['Tanggal', 'Kode Transaksi', 'Kasir', 'Pelanggan', 'Item Terjual', 'Metode Pembayaran', 'Total'];
      $col = 'A';
      foreach ($headers as $header) {
         $sheet->setCellValue($col . $row, $header);
         $sheet->getStyle($col . $row)->getFont()->setBold(true);
         $col++;
      }
      $row++;

      // ===============================
      // DATA TRANSAKSI
      // ===============================
      $mapMetode = [
         'offline' => 'Tunai',
         'online'  => 'QRIS',
      ];

      foreach ($transaksi as $item) {

         $metodePembayaran = $mapMetode[$item->metode_pembayaran]
            ?? ucfirst($item->metode_pembayaran); // fallback jika ada value lain

         $sheet->fromArray([
            $item->created_at->format('d-m-Y H:i'),
            $item->kode_transaksi,
            $item->kasir,
            $item->nama_pelanggan,
            $item->total_item,
            $metodePembayaran,
            // FORMAT RUPIAH
            'Rp' . number_format($item->total_harga, 0, ',', '.')
         ], null, "A{$row}");
         $row++;
      }

      // BORDER TRANSAKSI
      $sheet->getStyle("A3:G" . ($row - 1))
         ->getBorders()->getAllBorders()
         ->setBorderStyle(Border::BORDER_THIN);

      // =====================
      // REKAP (DI BAWAH TABEL)
      // =====================
      $rekapRow = $row + 1;

      $sheet->setCellValue("A{$rekapRow}", 'REKAP TRANSAKSI');
      $sheet->mergeCells("A{$rekapRow}:G{$rekapRow}");
      $sheet->getStyle("A{$rekapRow}")
         ->getFont()->setBold(true);

      $sheet->setCellValue("A" . ($rekapRow + 1), 'Total Item Terjual');
      $sheet->setCellValue("B" . ($rekapRow + 1), $rekap['total_item'] . ' item');

      $sheet->setCellValue("A" . ($rekapRow + 2), 'Total Penjualan');
      $sheet->setCellValue("B" . ($rekapRow + 2), 'Rp' . number_format($rekap['total_penjualan'], 0, ',', '.'));

      $sheet->setCellValue("A" . ($rekapRow + 3), 'Pembayaran Tunai');
      $sheet->setCellValue("B" . ($rekapRow + 3), 'Rp' . number_format($rekap['offline'], 0, ',', '.'));

      $sheet->setCellValue("A" . ($rekapRow + 4), 'Pembayaran QRIS');
      $sheet->setCellValue("B" . ($rekapRow + 4), 'Rp' . number_format($rekap['online'], 0, ',', '.'));
      // ⬇️ PENTING: geser cursor baris
      $row = $rekapRow + 5;
      // ===============================
      // JEDA
      // ===============================
      $row += 2;

      // ===============================
      // JUDUL PRODUK TERLARIS
      // ===============================
      $sheet->setCellValue("A{$row}", "PRODUK TERLARIS");
      $sheet->mergeCells("A{$row}:D{$row}");
      $sheet->getStyle("A{$row}")->getFont()->setBold(true);
      $row++;

      // ===============================
      // HEADER PRODUK
      // ===============================
      $headersProduk = ['Nama Produk', 'Varian', 'Total Terjual', 'Total Penjualan'];
      $col = 'A';
      foreach ($headersProduk as $header) {
         $sheet->setCellValue($col . $row, $header);
         $sheet->getStyle($col . $row)->getFont()->setBold(true);
         $col++;
      }
      $row++;

      // ===============================
      // DATA PRODUK
      // ===============================
      foreach ($produkTerlaris as $produk) {
         $sheet->fromArray([
            $produk->nama_produk,
            $produk->nama_varian,
            $produk->total_terjual,
            'Rp' . number_format($produk->total_penjualan, 0, ',', '.')
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

      /*
      |--------------------------------------------------------------------------
      | DOWNLOAD
      |--------------------------------------------------------------------------
      */
      $filename = 'data-transaksi-' .
         Str::slug($tenant->name) . '-' .
         $startDate->format('d-m-Y') . '-' .
         $endDate->format('d-m-Y') . '.xlsx';


      $writer = new Xlsx($spreadsheet);

      return response()->streamDownload(function () use ($writer) {
         $writer->save('php://output');
      }, $filename);
   }

   public function pembatalanDanRetur(Request $request)
   {
      // =========================
      // TENANT
      // =========================
      $tenant = $request->tenant;

      if (! $tenant->isActive) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $searchDate = $request->search_date; // format: YYYY-MM-DD

      // =========================
      // QUERY STOK TOKO KELUAR
      // =========================
      $tanggalAktif = $request->filled('search_date')
         ? Carbon::parse($request->search_date)
         : now();

      // =========================
      // QUERY STOK TOKO KELUAR
      // =========================
      $batalDanRetur = Validasi::with([
         'detailTransaksi.produkVarian',
         'transaksi.kasir',
         'admin'
      ])
         ->whereHas('transaksi', function ($q) use ($tenant, $tanggalAktif) {
            $q->where('tenant_id', $tenant->id)
               ->whereDate('created_at', $tanggalAktif);
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
      // dd($tenant, $bulanAktif, $stokKeluar);
      // =========================
      // TITLE
      // =========================
      $title = $tenant->name . ' | Stok Keluar';

      return view(
         'pages.admin.pembatalan-dan-retur.index',
         compact('tenant', 'batalDanRetur', 'totalHargaBatalRetur', 'title', 'tanggalAktif')
      );
   }

   public function catatan(Request $request)
   {
      $admin = auth()->user();

      // 1️⃣ Ambil tenant aktif
      $tenant = $request->tenant;

      if (!$tenant) {
         return back()->with('error', 'Tenant tidak ditemukan atau tidak aktif');
      }

      // 2️⃣ Cek apakah admin punya akses ke tenant ini
      $hasAccess = $admin->tenants()
         ->where('tenants.id', $tenant->id)
         ->exists();

      if (!$hasAccess) {
         return back()->with('error', 'Anda tidak memiliki akses ke tenant ini');
      }
      $title = $tenant->name . ' | Catatan';

      // 3️⃣ Ambil catatan berdasarkan tenant
      $catatan = Catatan::with(['kasir', 'admin'])
         ->where('tenant_id', $tenant->id)
         ->latest()
         ->get();

      return view('pages.admin.catatan.index', compact('catatan', 'title', 'tenant'));
   }

   public function toggleCatatan($id)
   {
      $admin = auth()->user();

      // 1️⃣ Ambil catatan + relasi tenant
      $catatan = Catatan::with('tenant')->findOrFail($id);

      // 2️⃣ Pastikan tenant catatan masih aktif
      if (!$catatan->tenant || !$catatan->tenant->isActive) {
         abort(403, 'Tenant tidak aktif');
      }

      // 3️⃣ Cek apakah admin punya akses ke tenant catatan
      $hasAccess = $admin->tenants()
         ->where('tenants.id', $catatan->tenant_id)
         ->exists();

      if (!$hasAccess && $admin->role !== 'superadmin') {
         abort(403, 'Anda tidak memiliki akses ke tenant ini');
      }

      // 4️⃣ Toggle logic
      if ($catatan->admin_id === null) {
         // ACC
         $catatan->update([
            'admin_id' => $admin->id,
         ]);
      } else {
         // Restore (batalkan ACC)
         $catatan->update([
            'admin_id' => null,
         ]);
      }

      return back()->with('success', 'Status catatan berhasil diperbarui');
   }

   public function printerIndex(Request $request)
   {

      $tenant = $request->tenant;
      if (! $tenant || ! $tenant->isActive) {
         return redirect()->route('admin.index')->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }
      $data = Printer::all();
      $title = 'Printer Setting';

      return view('pages.admin.printer.index', compact('tenant', 'data', 'title'));
   }

   public function printerStore(Request $request)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      // dd($request->all());
      $messages = [
         'printer.required' => 'Nama printer wajib diisi.',
         'printer.max' => 'Nama printer maksimal 30 karakter',
         'printer.unique' => 'Nama printer sudah digunakan.',

         'kertas.required' => 'Ukuran lebar kertas wajib diisi',
         'kertas.integer' => 'Ukuran lebar kertas harus berupa angka.',
      ];

      $validate = $request->validate([
         'printer' => 'required|string|max:30|unique:printer,nama_printer',
         'kertas' => 'required|integer',
      ], $messages);

      $save = Printer::create([
         'nama_printer' => $validate['printer'],
         'lebar_kertas' => $validate['kertas'],
      ]);

      if (! $save) {
         return redirect()->back()->with('error', 'Gagal menambahkan printer.');
      } else {
         return redirect()->back()->with('success', 'Printer berhasil ditambahkan.');
      }
   }

   public function printerUpdate(Request $request, $id)
   {
      $tenantId = session('active_tenant_id');

      if (!$tenantId) {
         return redirect()->route('admin.index')->with('error', 'Silakan pilih tenant dulu');
      }

      $messages = [
         'printer.required' => 'Nama printer wajib diisi.',
         'printer.max' => 'Nama printer maksimal 30 karakter',
         'printer.unique' => 'Nama printer sudah digunakan.',

         'kertas.required' => 'Ukuran lebar kertas wajib diisi',
         'kertas.integer' => 'Ukuran lebar kertas harus berupa angka.',
      ];

      $validate = $request->validate([
         'printer' => [
            'required',
            'string',
            'max:30',
            Rule::unique('printer', 'nama_printer')->ignore($id),
         ],
         'kertas' => 'required|integer',
      ], $messages);

      $printer = Printer::findOrFail($id);
      if (! $printer) {
         return redirect()->back()->with('error', 'Printer tidak ditemukan.');
      }
      $update = $printer->update([
         'nama_printer' => $validate['printer'],
         'lebar_kertas' => $validate['kertas'],
      ]);

      if (! $update) {
         return redirect()->back()->with('error', 'Gagal mengupdate printer.');
      } else {
         return redirect()->back()->with('success', 'Printer berhasil diupdate.');
      }
   }

   public function printerDelete($id)
   {
      $printer = Printer::findOrFail($id);
      if (! $printer) {
         return redirect()->back()->with('error', 'Printer tidak ditemukan.');
      }
      $delete = $printer->delete();

      if (! $delete) {
         return redirect()->back()->with('error', 'Gagal menghapus printer.');
      } else {
         return redirect()->back()->with('success', 'Printer berhasil dihapus.');
      }
   }
}
