<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Gudang;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\ProdukToko;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use App\Models\StokGudangMasuk;
use Illuminate\Validation\Rule;
use App\Models\StokGudangKeluar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Validated;
use function PHPUnit\Framework\fileExists;

class SuperAdminController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index(Request $request)
   {
      // ==========================
      // FILTER INPUT
      // ==========================
      $tenantId = $request->tenant_id ?? 'all';
      $selectedDate = $request->date ?? Carbon::today()->toDateString();

      // ==========================
      // LIST TENANT (DROPDOWN)
      // ==========================
      $tenants = Tenant::where('isActive', true)
         ->orderBy('name')
         ->get();

      // Default tenant
      if (!$tenantId && $tenants->count()) {
         $tenantId = $tenants->first()->id;
      }

      $tenant = $tenants->where('id', $tenantId)->first();

      // ==========================
      // FILTER INPUT (JAM KERJA)
      // ==========================
      $now = Carbon::now();

      // default tanggal kerja
      $selectedDate = $request->date
         ?? ($now->hour < 6
            ? $now->copy()->subDay()->toDateString()
            : $now->toDateString());

      $tenantId = $request->tenant_id ?? 'all';

      // ==========================
      // RANGE JAM KERJA
      // ==========================
      $start = Carbon::parse($selectedDate)->setTime(6, 0, 0);
      $end   = $start->copy()->addDay()->subMinute(); // 05:59

      // ==========================
      // TOTAL PRODUK TERJUAL
      // ==========================
      $totalProdukTerjual = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('transaksis.tenant_id', $tenantId);
         })
         ->sum('detail_transaksis.jumlah');


      // ==========================
      // TOTAL PENDAPATAN
      // ==========================
      $pendapatan = DB::table('transaksis')
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
         })
         ->sum('total_harga');


      // ==========================
      // TOTAL PRODUK RETUR
      // ==========================
      $totalProdukRetur = DB::table('stok_toko_keluar')
         ->whereBetween('created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
         })
         ->sum('jumlah_retur');


      // ==========================
      // TOTAL HARGA RETUR
      // ==========================
      $totalHargaRetur = DB::table('stok_toko_keluar')
         ->whereBetween('created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
         })
         ->sum(DB::raw('jumlah_retur * harga_produk'));


      // ==========================
      // TOTAL PENGELUARAN
      // ==========================
      $pengeluaran = DB::table('pengeluaran')
         ->whereBetween('created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
         })
         ->sum('jumlah');
      // dd($pengeluaran);

      $totalPengeluaran = $pengeluaran + $totalHargaRetur;


      // ==========================
      // LABA
      // ==========================
      $laba = $pendapatan - $totalPengeluaran;

      // ==========================
      // PRODUK TERLARIS
      // ==========================
      $produkTerlaris = DB::table('detail_transaksis')
         ->join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
         ->join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->whereBetween('transaksis.created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('transaksis.tenant_id', $tenantId);
         })
         ->select(
            DB::raw("CONCAT(produk.nama_produk, ' - ', produk_varian.nama) AS nama_produk"),
            DB::raw('SUM(detail_transaksis.jumlah) AS total_terjual')
         )
         ->groupBy(
            'produk.id',
            'produk.nama_produk',
            'produk_varian.id',
            'produk_varian.nama'
         )
         ->orderByDesc('total_terjual')
         ->limit(10)
         ->get();



      $produkKeluarToko = DB::table('stok_toko_keluar')
         ->join('produk_varian', 'produk_varian.id', '=', 'stok_toko_keluar.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->join('tenants', 'tenants.id', '=', 'produk_toko.tenant_id')
         ->whereBetween('stok_toko_keluar.created_at', [$start, $end])
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('produk_toko.tenant_id', $tenantId);
         })
         ->select(
            'tenants.id as tenant_id',
            'tenants.name as toko',
            'produk.id as produk_id',
            DB::raw("CONCAT(produk.nama_produk, ' - ', produk_varian.nama) AS nama_produk"),
            DB::raw('SUM(stok_toko_keluar.jumlah_retur) AS total_keluar'),
            DB::raw('SUM(stok_toko_keluar.jumlah_retur * stok_toko_keluar.harga_produk) AS total_harga'),
            DB::raw('GROUP_CONCAT(DISTINCT stok_toko_keluar.keterangan SEPARATOR ", ") AS keterangan'),
            DB::raw('DATE(stok_toko_keluar.created_at) AS tanggal')
         )
         ->groupBy(
            'tenants.id',
            'tenants.name',
            'produk.id',
            'produk.nama_produk',
            'produk_varian.id',
            'produk_varian.nama',
            DB::raw('DATE(stok_toko_keluar.created_at)')
         )
         ->orderByDesc('total_keluar')
         ->limit(10)
         ->get();


      // dd($produkKeluarToko);

      $aktivitasGudangMasuk = DB::table('stok_gudang_masuk')
         ->join('gudang', 'gudang.id', '=', 'stok_gudang_masuk.gudang_id')
         ->join('produk', 'produk.id', '=', 'stok_gudang_masuk.produk_id')
         ->join('users', 'users.id', '=', 'stok_gudang_masuk.user_id')
         ->whereDate('stok_gudang_masuk.created_at', $selectedDate)
         ->select(
            'stok_gudang_masuk.id',
            'stok_gudang_masuk.qty_masuk',
            'stok_gudang_masuk.harga_pembelian',
            'stok_gudang_masuk.created_at',
            'gudang.name as gudang',
            'produk.nama_produk',
            'users.name as user',
         )
         ->orderBy('stok_gudang_masuk.created_at', 'desc')
         ->limit(10)
         ->get();

      $aktivitasGudangKeluar = DB::table('stok_gudang_keluar')
         ->join('gudang', 'gudang.id', '=', 'stok_gudang_keluar.gudang_id')
         ->join('produk', 'produk.id', '=', 'stok_gudang_keluar.produk_id')
         ->whereDate('stok_gudang_keluar.created_at', $selectedDate)
         ->select(
            'stok_gudang_keluar.id',
            'stok_gudang_keluar.created_at',
            'stok_gudang_keluar.qty_keluar',
            'stok_gudang_keluar.keterangan',
            'produk.nama_produk',
            'gudang.name as gudang'
         )
         ->orderByDesc('stok_gudang_keluar.created_at')
         ->limit(10)
         ->get();

      // ==========================
      // AKTIVITAS TRANSAKSI
      // ==========================
      // $aktivitasTransaksi = DB::table('transaksis')
      //    ->join('users', 'users.id', '=', 'transaksis.kasir_id')
      //    ->join('detail_transaksis', 'detail_transaksis.transaksi_id', '=', 'transaksis.id')
      //    ->whereBetween('transaksis.created_at', [$start, $end])
      //    ->when($tenantId !== 'all', function ($q) use ($tenantId) {
      //       $q->where('transaksis.tenant_id', $tenantId);
      //    })
      //    ->select(
      //       'transaksis.kode_transaksi',
      //       'transaksis.nama_pelanggan',
      //       'transaksis.kasir_id',
      //       'transaksis.total_harga',
      //       'transaksis.created_at',
      //       'users.name as nama_kasir',
      //       DB::raw('SUM(detail_transaksis.jumlah) AS total_item')
      //    )
      //    ->groupBy(
      //       'transaksis.kode_transaksi',
      //       'transaksis.nama_pelanggan',
      //       'transaksis.kasir_id',
      //       'transaksis.total_harga',
      //       'transaksis.created_at',
      //       'users.name'
      //    )
      //    ->orderByDesc('transaksis.created_at')
      //    ->limit(10)
      //    ->get();

      $bulanIni = Carbon::parse($selectedDate)->month;
      $tahunIni = Carbon::parse($selectedDate)->year;

      $pendapatanBulanan = DB::table('transaksis')
         ->whereMonth('created_at', $bulanIni)
         ->whereYear('created_at', $tahunIni)
         ->when(
            $tenantId !== 'all',
            fn($q) =>
            $q->where('tenant_id', $tenantId)
         )
         ->select(
            DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) as tanggal'),
            DB::raw('SUM(total_harga) as total')
         )
         ->groupBy(DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))'))
         ->pluck('total', 'tanggal');


      // $retur = DB::table('stok_toko_keluar')
      //    ->whereMonth('created_at', $bulanIni)
      //    ->whereYear('created_at', $tahunIni)
      //    ->when($tenantId !== 'all', function ($q) use ($tenantId) {
      //       $q->where('tenant_id', $tenantId);
      //    })
      //    ->select(
      //       DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) as tanggal'),
      //       DB::raw('SUM(jumlah_retur * harga_produk) as total')
      //    )
      //    ->groupBy(DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))'))
      //    ->pluck('total', 'tanggal');


      $pengeluaranBulanan = DB::table('pengeluaran')
         ->whereMonth('created_at', $bulanIni)
         ->whereYear('created_at', $tahunIni)
         ->when($tenantId !== 'all', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
         })
         ->select(
            DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR)) as tanggal'),
            DB::raw('SUM(jumlah) as total')
         )
         ->groupBy(DB::raw('DATE(DATE_SUB(created_at, INTERVAL 6 HOUR))'))
         ->pluck('total', 'tanggal');

      $labels = [];
      $seriesPendapatan = [];
      $seriesPengeluaran = [];

      $start = now()->startOfMonth();
      $end   = now()->endOfMonth();

      for ($date = $start; $date <= $end; $date->addDay()) {

         $tgl = $date->toDateString();

         $labels[] = $date->translatedFormat('d');

         // Pendapatan
         $seriesPendapatan[] = (int) ($pendapatanBulanan[$tgl] ?? 0);

         // Pengeluaran = retur + pengeluaran manual
         $totalRetur = (int) ($retur[$tgl] ?? 0);
         $totalPengeluaran = (int) ($pengeluaranBulanan[$tgl] ?? 0);

         $seriesPengeluaran[] = $totalRetur + $totalPengeluaran;
      }


      $title = 'Dashboard';

      return view('pages.superadmin.index', compact(
         'title',
         'tenants',
         'tenant',
         'selectedDate',
         'totalProdukTerjual',
         'pendapatan',
         'totalProdukRetur',
         'totalHargaRetur',
         'pengeluaran',
         'totalPengeluaran',
         'laba',
         'produkTerlaris',
         'produkKeluarToko',
         // 'aktivitasTransaksi',
         'aktivitasGudangMasuk',
         'aktivitasGudangKeluar',
         'labels',
         'seriesPendapatan',
         'seriesPengeluaran',
         'tenantId'
      ));
   }

   public function pengeluaran(Request $request)
   {
      $query = Pengeluaran::query();

      // 1️⃣ Filter berdasarkan toko
      if ($request->filled('toko_id')) {
         $query->where('tenant_id', $request->toko_id);
      }

      // 2️⃣ Filter berdasarkan 1 tanggal (created_at)
      if ($request->filled('tanggal')) {

         $query->whereDate('created_at', $request->tanggal);
         $tanggalAktif = $request->tanggal;
      } else {

         // 🔥 DEFAULT: hari ini
         $query->whereDate('created_at', now()->toDateString());
         $tanggalAktif = now()->toDateString();
      }

      // 3️⃣ Ambil data
      $pengeluaran = $query
         ->orderBy('created_at', 'DESC')
         ->paginate(10);

      // 4️⃣ Data pendukung view
      $toko  = Tenant::all();
      $title = 'Pengeluaran';

      return view(
         'pages.superadmin.pengeluaran.index',
         compact('pengeluaran', 'toko', 'title', 'tanggalAktif')
      );
   }


   public function pendapatan(Request $request)
   {
      // =========================
      // LIST TENANT AKTIF
      // =========================
      $tenants = Tenant::where('isActive', true)
         ->orderBy('name')
         ->get();

      if ($tenants->isEmpty()) {
         return redirect()->route('superadmin.index')
            ->with('error', 'Tidak ada tenant aktif.');
      }

      // =========================
      // TENANT TERPILIH (DEFAULT)
      // =========================
      $tenantId = $request->tenant_id ?? $tenants->first()->id;
      $tenant   = $tenants->firstWhere('id', $tenantId);

      if (! $tenant) {
         return redirect()->route('superadmin.pendapatan')
            ->with('error', 'Tenant tidak valid.');
      }

      $title = 'Pendapatan';

      // =========================
      // DEFAULT TANGGAL BERDASARKAN JAM KERJA
      // =========================
      $now = Carbon::now();

      // sebelum jam 06.00 → business day masih kemarin
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

      if ($startDate->gt($endDate)) {
         return back()
            ->withInput()
            ->withErrors([
               'tanggal' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'
            ]);
      }

      // =========================
      // SHIFT RANGE (06.00 - 05.59)
      // =========================
      $startDateShift = $startDate->copy()->addHours(6);
      $endDateShift   = $endDate->copy()->addHours(6);

      // =========================
      // QUERY LAPORAN (SAMA DENGAN ADMIN)
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

      return view('pages.superadmin.pendapatan.index', [
         'tenants'      => $tenants,
         'tenant'       => $tenant,
         'laporan'      => $laporan,
         'totalBulanan' => $totalBulanan,
         'startDate'    => $startDate->toDateString(),
         'endDate'      => $endDate->toDateString(),
         'title'        => $title,
      ]);
   }


   public function adminIndex()
   {
      $title = 'Manajemen Admin';
      if (auth()->user()->id === 1) {
         $data = User::with('tenants')->get();
      } else {
         $data = User::with('tenants')->where('role', 'admin')->get();
      }
      $daftarToko = Tenant::all();
      return view('pages.superadmin.admin.index', compact('title', 'data', 'daftarToko'));
   }

   public function adminStore(Request $request)
   {
      // dd($request->all());
      $messages = [
         'name.required' => 'Nama admin wajib diisi.',
         'name.max' => 'Nama admin maksimal 50 karakter.',

         'username.required' => 'Username wajib diisi.',
         'username.max' => 'Username maksimal 30 karakter.',
         'username.unique' => 'Username sudah digunakan oleh admin lain.',

         'email.required' => 'Email wajib diisi.',
         'email.email' => 'Format email tidak valid.',
         'email.unique' => 'Email sudah digunakan oleh admin lain.',

         'password.required' => 'Password wajib diisi.',
         'password.min' => 'Password minimal 6 karakter.',

         'toko.required' => 'Minimal pilih satu toko.',
         'toko.array' => 'Format toko tidak valid.',
         'toko.exists' => 'Toko tidak ditemukan.',
      ];

      $validated = $request->validate(
         [
            'name' => 'required|string|max:50',
            'username' => [
               'required',
               'string',
               'max:30',
               Rule::unique('users', 'username')->where('role', 'admin'),
            ],
            'email' => [
               'required',
               'email',
               Rule::unique('users', 'email')->where('role', 'admin'),
            ],
            'password' => 'required|string|min:6',
            'toko' => 'required|array|min:1',
            'toko.*' => 'exists:tenants,id',
         ],
         $messages
      );

      // dd($validated);

      DB::beginTransaction();

      try {
         // 1️⃣ Simpan user admin
         $admin = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'isActive' => true,
         ]);

         // 2️⃣ Simpan relasi ke pivot (multi-tenant)
         $admin->tenants()->sync($validated['toko']);

         DB::commit();

         return redirect()
            ->route('superadmin.admin')
            ->with('success', 'Admin berhasil ditambahkan.');
      } catch (\Throwable $e) {
         DB::rollBack();

         return back()
            ->withInput()
            ->with('error', 'Terjadi kesalahan saat menyimpan data admin.');
      }
   }

   public function adminEdit($id)
   {
      $title = 'Edit Admin';
      if (auth()->user()->id === 1) {
         $admin = User::with('tenants')->findOrFail($id);
      } else {
         $admin = User::with('tenants')->where('role', 'admin')->findOrFail($id);
      }

      if (!$admin) {
         return redirect()->route('superadmin.admin')->with('error', 'Admin tidak ditemukan.');
      }

      $daftarToko = Tenant::all();

      return view('pages.superadmin.admin.edit', compact('title', 'admin', 'daftarToko'));
   }

   public function adminUpdate(Request $request, $id)
   {
      $admin = User::where('id', $id)
         ->first();

      if (! $admin) {
         return back()->with('error', 'User tidak ditemukan.');
      }

      $tenantIds = $request->input('toko', []);

      // 🔑 Hanya admin & kasir yang wajib punya tenant
      if (in_array($admin->role, ['admin', 'kasir']) && empty($tenantIds)) {
         return back()->with('error', 'User dengan role admin atau kasir harus memiliki minimal satu tenant.');
      }

      $messages = [
         'name.required' => 'Nama user wajib diisi.',
         'name.max' => 'Nama user maksimal 50 karakter.',

         'username.required' => 'Username wajib diisi.',
         'username.max' => 'Username maksimal 30 karakter.',
         'username.unique' => 'Username sudah digunakan oleh user lain.',

         'email.required' => 'Email wajib diisi.',
         'email.email' => 'Format email tidak valid.',
         'email.unique' => 'Email sudah digunakan oleh user lain.',

         'password.required' => 'Password wajib diisi.',
         'password.min' => 'Password minimal 6 karakter.',

         'toko.array' => 'Format toko tidak valid.',
         'toko.exists' => 'Toko tidak ditemukan.',
      ];

      // ================= VALIDASI =================
      $request->validate([
         'name' => 'required|string|max:25',

         'username' => [
            'required',
            'string',
            'min:4',
            'max:20',
            Rule::unique('users', 'username')
               ->ignore($admin->id)
               ->where('role', 'admin'),
         ],

         'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')
               ->ignore($admin->id)
               ->where('role', 'admin'),
         ],

         'password' => 'nullable|string|min:6',

         'toko' => 'array|min:1',
         'toko.*' => 'exists:tenants,id',

      ], $messages);

      // ================= UPDATE USER =================
      $data = [
         'name' => $request->name,
         'username' => $request->username,
         'email' => $request->email,
      ];

      // Update password hanya jika diisi
      if ($request->filled('password')) {
         $data['password'] = Hash::make($request->password);
      }

      $admin->update($data);

      // ================= SYNC TENANT =================
      $admin->tenants()->sync($tenantIds);

      return redirect()
         ->route('superadmin.admin')
         ->with('success', 'User berhasil diperbarui.');
   }

   public function adminStatus($id)
   {
      if (auth()->user()->id === 1) {
         $admin = User::findOrFail($id);
      } else {
         $admin = User::where('role', 'admin')->findOrFail($id);
      }

      if (!$admin) {
         return back()->with('error', 'Admin tidak ditemukan.');
      }

      // Toggle status
      $admin->isActive = ! $admin->isActive;
      $admin->save();

      $message = $admin->isActive
         ? 'Admin berhasil diaktifkan kembali.'
         : 'Admin berhasil dinonaktifkan.';

      return back()->with('success', $message);
   }

   public function tokoIndex()
   {
      $title = 'Manajemen Toko';
      $data = Tenant::all();
      return view('pages.superadmin.toko.index', compact('title', 'data'));
   }
   public function addToko()
   {
      $title = 'Tambah Toko';
      return view('pages.superadmin.toko.add', compact('title'));
   }

   public function storeToko(Request $request)
   {
      // dd($request->all());
      $messages = [
         'nama_toko.required' => 'Nama toko wajib diisi.',
         'nama_toko.max' => 'Nama toko maksimal 50 karakter.',
         'nama_toko.unique' => 'Nama toko sudah digunakan. Silakan gunakan nama lain.',
         'alamat.required' => 'Alamat wajib diisi.',
         'tiktok.max' => 'Akun TikTok maksimal 20 karakter.',
         'instagram.max' => 'Akun Instagram maksimal 20 karakter.',
         'filepond.image' => 'Logo harus berupa file gambar.',
         'filepond.mimes' => 'Logo harus berformat jpg, jpeg, atau png.',
         'filepond.max' => 'Ukuran logo maksimal 5MB.',
      ];

      $validate = $request->validate(
         [
            'nama_toko' => 'required|string|max:50|unique:tenants,name',
            'alamat' => 'required|string|max:500',
            'tiktok' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:20',
            'filepond' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
         ],
         $messages
      );

      // --- HANDLE UPLOAD LOGO ---
      $logoName = null;

      if ($request->hasFile('filepond')) {
         $logoFile = $request->file('filepond');

         // Nama file: logo_timestamp_random.ext
         $logoName = 'logo_' . time() . '_' . uniqid() . '.' .
            $logoFile->getClientOriginalExtension();

         $path = base_path('public/assets/logo');

         // Pastikan folder ada
         if (!file_exists($path)) {
            mkdir($path, 0755, true);
         }
         // Upload logo baru
         $logoFile->move($path, $logoName);
         // Simpan ke folder public/assets/logo
         // $logoFile->move(public_path('assets/logo'), $logoName);
      }

      // --- SIMPAN DATA TOKO ---
      $toko = Tenant::create([
         'name' => $validate['nama_toko'],
         'address' => $validate['alamat'],
         'tiktok' => $validate['tiktok'] ?? null,
         'instagram' => $validate['instagram'] ?? null,
         'logo' => $logoName, // bisa null jika tidak upload
         'isActive' => 1,
      ]);

      if ($toko) {
         return redirect()->route('superadmin.toko')->with('success', 'Toko berhasil ditambahkan.');
      } else {
         return back()->with('error', 'Terjadi kesalahan saat menambahkan toko. Silakan coba lagi.');
      }
   }

   public function editToko($id)
   {
      $title = 'Edit Toko';
      $data = Tenant::findOrFail($id);
      if (!$data) {
         return redirect()->route('superadmin.toko')->with('error', 'Toko tidak ditemukan.');
      }
      return view('pages.superadmin.toko.edit', compact('title', 'data'));
   }

   public function updateToko(Request $request, $id)
   {
      $toko = Tenant::findOrFail($id);
      if (!$toko) {
         return redirect()->route('superadmin.toko')->with('error', 'Toko tidak ditemukan.');
      }
      // VALIDASI
      $messages = [
         'nama_toko.required' => 'Nama toko wajib diisi.',
         'alamat.required' => 'Alamat wajib diisi.',
         'tiktok.max' => 'Akun TikTok maksimal 20 karakter.',
         'instagram.max' => 'Akun Instagram maksimal 20 karakter.',
         'filepond.image' => 'Logo harus berupa file gambar.',
         'filepond.mimes' => 'Logo harus berformat jpg, jpeg, atau png.',
         'filepond.max' => 'Ukuran logo maksimal 5MB.',
      ];

      $validate = $request->validate(
         [
            'nama_toko' => [
               'required',
               'string',
               'max:50',
               Rule::unique('tenants', 'name')->ignore($id) // abaikan nama lama
            ],
            'alamat' => 'required|string|max:500',
            'tiktok' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:20',
            'filepond' => 'nullable|image|mimes:jpg,jpeg,png|max:5120'
         ],
         $messages
      );

      // SIAPKAN DATA UPDATE
      $dataToUpdate = [
         'name' => $validate['nama_toko'],
         'address' => $validate['alamat'],
         'tiktok' => $validate['tiktok'] ?? null,
         'instagram' => $validate['instagram'] ?? null,
         'isActive' => $request->has('status') ? '1' : '0',
      ];

      // HANDLE UPDATE LOGO
      if ($request->hasFile('filepond')) {

         $logoFile = $request->file('filepond');
         $ext = $logoFile->getClientOriginalExtension();

         // Buat nama file logo baru
         $logoName = 'logo_' . time() . '_' . uniqid() . '.' . $ext;

         $path = base_path('public/assets/logo');

         // Pastikan folder ada
         if (!file_exists($path)) {
            mkdir($path, 0755, true);
         }

         // Hapus logo lama
         if ($toko->logo && file_exists($path . '/' . $toko->logo)) {
            unlink($path . '/' . $toko->logo);
         }

         // Upload logo baru
         $logoFile->move($path, $logoName);

         // Simpan nama logo di database
         $dataToUpdate['logo'] = $logoName;
      }

      // UPDATE DATA TOKO
      $update = $toko->update($dataToUpdate);
      if ($update) {
         return redirect()->back()->with('success', 'Data toko berhasil diperbarui.');
      } else {
         return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data toko. Silakan coba lagi.');
      }
   }

   public function gudang()
   {
      $title = 'Manajemen Gudang';
      $data = Gudang::with('tenants')->get();
      $daftarToko = Tenant::whereDoesntHave('gudangs')->get();
      return view('pages.superadmin.gudang.index', compact('title', 'data', 'daftarToko'));
   }

   public function gudangStore(Request $request)
   {
      $messages = [
         'gudang.required' => 'Nama gudang wajib diisi.',
         'gudang.max'      => 'Nama gudang maksimal 50 karakter.',
         'gudang.unique'   => 'Nama gudang sudah ada.',
         'toko.required'   => 'Minimal pilih satu toko.',
         'toko.array'      => 'Format toko tidak valid.',
         'toko.*.exists'   => 'Toko tidak ditemukan.',
      ];

      $validated = $request->validate([
         'gudang' => [
            'required',
            'string',
            'max:50',
            'unique:gudang,name',
         ],
         'toko'   => 'required|array|min:1',
         'toko.*' => 'exists:tenants,id',
      ], $messages);

      try {

         DB::transaction(function () use ($validated) {

            // 1️⃣ Simpan gudang
            $gudang = Gudang::create([
               'name' => $validated['gudang'],
            ]);

            // 🔴 Jika gagal create gudang
            if (! $gudang) {
               throw new \Exception('Gagal menyimpan data gudang.');
            }

            // 2️⃣ Simpan relasi gudang ↔ tenant
            foreach ($validated['toko'] as $tenantId) {
               $insert = DB::table('gudang_has_tenants')->insert([
                  'gudang_id'  => $gudang->id,
                  'tenant_id'  => $tenantId,
                  'created_at' => now(),
                  'updated_at' => now(),
               ]);

               // 🔴 Jika salah satu insert pivot gagal
               if (! $insert) {
                  throw new \Exception('Gagal menyimpan akses toko ke gudang.');
               }
            }
         });
      } catch (\Throwable $e) {

         return back()
            ->withInput()
            ->with('error', $e->getMessage());
      }

      return back()->with('success', 'Gudang berhasil ditambahkan.');
   }

   public function gudangEdit($id)
   {
      // dd($id);
      $title = 'Edit Gudang';
      $gudang = Gudang::findOrFail($id);
      $daftarToko = Tenant::whereDoesntHave('gudangs')
         ->orWhereHas('gudangs', function ($q) use ($id) {
            $q->where('gudang_id', $id);
         })
         ->get();

      return view('pages.superadmin.gudang.edit', compact('title', 'gudang', 'daftarToko'));
   }

   public function gudangUpdate(Request $request, $id)
   {
      $messages = [
         'gudang.required' => 'Nama gudang wajib diisi.',
         'gudang.max'      => 'Nama gudang maksimal 50 karakter.',
         'gudang.unique'   => 'Nama gudang sudah ada.',
         'toko.required'   => 'Minimal pilih satu toko.',
         'toko.array'      => 'Format toko tidak valid.',
         'toko.*.exists'   => 'Toko tidak ditemukan.',
      ];

      $validated = $request->validate([
         'gudang' => [
            'required',
            'string',
            'max:50',
            Rule::unique('gudang', 'name')->ignore($id),
         ],
         'toko'   => 'required|array|min:1',
         'toko.*' => 'exists:tenants,id',
      ], $messages);

      try {
         // =========================
         // VALIDASI PENGHAPUSAN TOKO
         // =========================

         // toko lama yang sudah terhubung ke gudang ini
         $tokoLama = DB::table('gudang_has_tenants')
            ->where('gudang_id', $id)
            ->pluck('tenant_id')
            ->toArray();

         // toko baru dari request
         $tokoBaru = $validated['toko'];

         // toko yang AKAN DIHAPUS (ada di lama, tapi tidak ada di baru)
         $tokoDihapus = array_diff($tokoLama, $tokoBaru);

         foreach ($tokoDihapus as $tenantId) {

            $punyaProduk = DB::table('produk_toko')
               ->where('tenant_id', $tenantId)
               ->exists();

            if ($punyaProduk) {
               throw new \Exception(
                  'Akses gudang tidak dapat dihapus karena salah satu toko sudah memiliki produk.'
               );
            }
         }

         DB::transaction(function () use ($validated, $id) {

            // 1️⃣ Update nama gudang
            $gudang = Gudang::findOrFail($id);

            $updated = $gudang->update([
               'name' => $validated['gudang'],
            ]);

            if (! $updated) {
               throw new \Exception('Gagal mengupdate nama gudang.');
            }

            // 2️⃣ Update akses toko (SYNC)
            // hapus lama → insert baru
            DB::table('gudang_has_tenants')
               ->where('gudang_id', $id)
               ->delete();

            foreach ($validated['toko'] as $tenantId) {
               $insert = DB::table('gudang_has_tenants')->insert([
                  'gudang_id'  => $id,
                  'tenant_id'  => $tenantId,
                  'created_at' => now(),
                  'updated_at' => now(),
               ]);

               if (! $insert) {
                  throw new \Exception('Gagal memperbarui akses toko.');
               }
            }
         });
      } catch (\Throwable $e) {

         return back()
            ->withInput()
            ->with('error', $e->getMessage());
      }

      return redirect()
         ->route('superadmin.gudang')
         ->with('success', 'Gudang berhasil diperbarui.');
   }

   public function barangMasuk(Request $request)
   {
      // =========================
      // DATA UNTUK FILTER
      // =========================
      $daftarGudang = Gudang::orderBy('name')->get();

      // =========================
      // TENTUKAN TANGGAL AKTIF
      // =========================
      if ($request->filled('tanggal')) {
         // Format: YYYY-MM-DD
         $tanggalAktif = $request->tanggal;
         $tanggal = Carbon::createFromFormat('Y-m-d', $request->tanggal);
      } else {
         // 🔥 DEFAULT: TANGGAL SAAT INI
         $tanggalAktif = now()->format('Y-m-d');
         $tanggal = now();
      }

      // =========================
      // QUERY UTAMA
      // =========================
      $query = StokGudangMasuk::with(['produk', 'gudang', 'admin'])
         ->whereDate('created_at', $tanggal->toDateString());

      // 🔹 Filter Gudang
      if ($request->filled('gudang')) {
         $query->where('gudang_id', $request->gudang);
      }

      // =========================
      // DATA RIWAYAT
      // =========================
      $riwayat = $query
         ->latest()
         ->paginate(10)
         ->withQueryString();

      // =========================
      // TOTAL (TIDAK TERPENGARUH PAGINATION)
      // =========================
      $total = (clone $query)
         ->selectRaw('
         COALESCE(SUM(qty_masuk), 0) as total_qty_masuk,
         COALESCE(SUM(harga_pembelian), 0) as total_harga_pembelian
      ')
         ->first();

      $title = 'Barang Masuk';

      return view(
         'pages.superadmin.stok-gudang-masuk.index',
         compact(
            'title',
            'riwayat',
            'daftarGudang',
            'total',
            'tanggalAktif'
         )
      );
   }

   public function barangKeluar(Request $request)
   {
      // =========================
      // DATA UNTUK FILTER
      // =========================
      $daftarGudang = Gudang::orderBy('name')->get();

      // =========================
      // TENTUKAN TANGGAL AKTIF
      // =========================
      if ($request->filled('tanggal')) {
         // Format: YYYY-MM-DD
         $tanggalAktif = $request->tanggal;
         $tanggal = Carbon::createFromFormat('Y-m-d', $request->tanggal);
      } else {
         // 🔥 DEFAULT: TANGGAL SAAT INI
         $tanggalAktif = now()->format('Y-m-d');
         $tanggal = now();
      }

      // =========================
      // QUERY UTAMA
      // =========================
      $query = StokGudangKeluar::with(['produk', 'gudang', 'admin'])
         ->whereDate('created_at', $tanggal->toDateString());

      // 🔹 Filter Gudang
      if ($request->filled('gudang')) {
         $query->where('gudang_id', $request->gudang);
      }

      // =========================
      // DATA RIWAYAT
      // =========================
      $riwayat = $query
         ->latest()
         ->paginate(10)
         ->withQueryString();

      // =========================
      // TOTAL (TIDAK TERPENGARUH PAGINATION)
      // =========================
      $total = (clone $query)
         ->selectRaw('
         COALESCE(SUM(qty_keluar), 0) as total_qty_keluar
      ')
         ->first();

      $title = 'Barang Keluar';

      return view(
         'pages.superadmin.stok-gudang-keluar.index',
         compact(
            'title',
            'riwayat',
            'daftarGudang',
            'total',
            'tanggalAktif'
         )
      );
   }

   public function logGudang(Request $request)
   {

      // =========================
      // DATA GUDANG
      // =========================
      $gudang = Gudang::orderBy('name')->get();

      // =========================
      // GUDANG AKTIF (DEFAULT)
      // =========================
      $gudangAktifId = $request->filled('gudang_id')
         ? $request->gudang_id
         : $gudang->first()?->id; // default gudang pertama

      if (! $gudangAktifId) {
         return back()->with('error', 'Tidak ada gudang tersedia.');
      }
      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->toDateString()
         : now()->toDateString();

      // =========================
      // QUERY LOG GUDANG (PER PRODUK)
      // =========================
      $log = Produk::where('gudang_id', $gudangAktifId)
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
      // dd($log);

      $title = 'Log Gudang';

      return view(
         'pages.superadmin.log-gudang.log',
         compact('gudang', 'title', 'log', 'tanggalAktif', 'gudangAktifId')
      );
   }

   public function detailLogGudang(Request $request, $produkId)
   {
      // 🔐 Gudang
      $gudang = Gudang::where('id', $request->gudang_id)->first();

      if (! $gudang) {
         return back()->with('error', 'Gudang tidak ditemukan.');
      }
      // dd($gudang);

      // =========================
      // VALIDASI PRODUK MILIK GUDANG TENANT
      // =========================
      $produk = Produk::where('id', $produkId)
         ->where('gudang_id', $gudang->id)
         ->first();
      if (! $produk) {
         return back()->with('error', 'Produk tidak ditemukan di gudang tersebut.');
      }

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

      $title = 'Detail Log Gudang';

      return view(
         'pages.superadmin.log-gudang.detail-log',
         compact(
            'gudang',
            'produk',
            'logFinal',
            'tanggalAktif',
            'title'
         )
      );
   }

   public function logToko(Request $request)
   {
      $tenants = Tenant::orderBy('name')->get();
      // =========================
      // CEK TENANT
      // =========================
      $tenant = Tenant::where('id', $request->tenant_id)->first();
      if (! $tenant && $request->filled('tenant_id')) {
         return back()->with('error', 'Toko tidak ditemukan.');
      }

      $tenantAktifId = $request->filled('tenant_id')
         ? $request->tenant_id
         : $tenants->first()?->id; // default tenant pertama

      // =========================
      // TANGGAL AKTIF (DEFAULT: HARI INI)
      // =========================
      $tanggalAktif = $request->filled('tanggal')
         ? Carbon::parse($request->tanggal)->format('Y-m-d')
         : now()->format('Y-m-d');

      // =========================
      // SUBQUERY STOK MASUK TOKO
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
         ->where('stok.tenant_id', $tenantAktifId)
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
         ->where('tenant_id', $tenantAktifId)
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
         ->where('transaksis.tenant_id', $tenantAktifId)
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

         ->where('produk_toko.tenant_id', $tenantAktifId)
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


      $title = 'Log Toko';

      return view(
         'pages.superadmin.log-toko.log',
         compact('tenants', 'tenantAktifId', 'tenant', 'logStok', 'tanggalAktif', 'title')
      );
   }

   public function detailLogToko($tenantId, $produkVarianId, Request $request)
   {
      // =========================
      // VALIDASI TENANT (FIX)
      // =========================
      $tenant = Tenant::where('id', $tenantId)->first();
      if (! $tenant && $request->filled('tenant_id')) {
         return back()->with('error', 'Toko tidak ditemukan.');
      }
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

      // =========================
      // VIEW SUPERADMIN (FIX)
      // =========================
      return view('pages.superadmin.log-toko.detail-log', [
         'tenant'         => $tenant,
         'produkVarian'     => $produkVarian,
         'logs'           => $logs,
         'tanggalAktif'   => $tanggalAktif,
         'stokAwal'       => $stokAwal,
         'produkTerjual'  => $produkTerjual
      ]);
   }
}
