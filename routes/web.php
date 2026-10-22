<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StrukController;
use App\Http\Controllers\ExportsController;
use App\Http\Controllers\AllUsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\SuperAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/403', function () {
//    return response()->view('error.403', [], 403);
// })->name('error.403');

Route::middleware(['auth'])->group(function () {
   Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
   Route::get('/profil/{id}', [AllUsersController::class, 'profileEdit'])->name('profile.edit');
   Route::put('/profil/update/{id}', [AllUsersController::class, 'profilUpdate'])->name('profile.update');
   Route::get('/transaksi/cek_struk/{id}', [StrukController::class, 'cekStruk'])->name('cek_struk');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
   Route::get('/toko', [AdminController::class, 'index'])->name('admin.index');
   Route::post('/toko/{tenant}', [AdminController::class, 'setTenant'])
      ->name('admin.set-tenant');
});


Route::middleware(['auth', 'role:admin', 'tenant.active'])
   ->group(function () {
      Route::get('/toko/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

      // kategori
      Route::get('/toko/gudang/kategori', [AdminController::class, 'kategori'])->name('admin.kategori');
      Route::post('/toko/gudang/kategori/store', [AdminController::class, 'storeKategori'])->name('admin.kategori.store');
      Route::put('/toko/gudang/kategori/update/{id}', [AdminController::class, 'updateKategori'])->name('admin.kategori.update');
      Route::get('/toko/gudang/kategori/delete/{id}', [AdminController::class, 'deleteKategori'])->name('admin.kategori.delete');

      // gudang
      Route::get('/toko/gudang/produk', [AdminController::class, 'produkGudang'])->name('admin.gudang');
      Route::get(
         '/toko/gudang/log',
         [AdminController::class, 'logGudang']
      )->name('admin.gudang.log');
      Route::get(
         'toko/gudang/log/detail/{produkId}',
         [AdminController::class, 'detailLogGudang']
      )->name('admin.gudang.detail-log');

      // produk gudang
      Route::get('/toko/gudang/produk/add', [AdminController::class, 'produkGudangAdd'])->name('admin.produk.add');
      Route::post('/toko/gudang/produk/store', [AdminController::class, 'storeProdukGudang'])->name('admin.produk.store');
      Route::get('/toko/gudang/produk/edit/{produkId}', [AdminController::class, 'produkGudangEdit'])->name('admin.produk.edit');
      Route::put('/toko/gudang/produk/update/{produkId}', [AdminController::class, 'updateProdukGudang'])->name('admin.produk.update');
      Route::get('/toko/gudang/produk/delete/{id}', [AdminController::class, 'deleteProdukGudang'])->name('admin.produk.delete');

      // stok gudang masuk
      Route::get(
         'toko/{id}/gudang/produk/stok-masuk/export',
         [ExportsController::class, 'exportStokGudangMasuk']
      )->name('admin.gudang.stok-masuk.export');

      Route::get('toko/gudang/stok-masuk', [AdminController::class, 'historiStokGudangMasuk'])->name('admin.gudang.stok-masuk');

      Route::post('toko/gudang/produk/stok/store', [
         AdminController::class,
         'storeStokGudang'
      ])->name('admin.gudang.stok.store');

      Route::put('/toko/gudang/produk/stok/update/{produkId}', [AdminController::class, 'updateStokGudang'])->name('admin.gudang.stok.update');

      Route::get('/toko/gudang/produk/stok/delete/{stokId}', [
         AdminController::class,
         'deleteStokGudang'
      ])->name('admin.gudang.stok.destroy');

      // stok gudang keluar
      Route::get(
         'toko/{id}/gudang/produk/stok-keluar/export',
         [ExportsController::class, 'exportStokGudangKeluar']
      )->name('admin.gudang.stok-keluar.export');

      Route::get(
         'toko/gudang/produk/stok-keluar',
         [AdminController::class, 'historiStokGudangKeluar']
      )->name('admin.gudang.stok-keluar');

      Route::get(
         'toko/gudang/produk/stok-keluar/add',
         [AdminController::class, 'addStokGudangKeluar']
      )->name('admin.gudang.stok-keluar.add');

      Route::post('toko/gudang/produk/stok-keluar/store', [
         AdminController::class,
         'storeStokGudangKeluar'
      ])->name('admin.gudang.stok-keluar.store');

      Route::get(
         'toko/gudang/produk/stok-keluar/edit/{id}',
         [AdminController::class, 'editStokGudangKeluar']
      )->name('admin.gudang.stok-keluar.edit');

      Route::get('/toko/gudang/produk/stok-keluar/update/{id}', [AdminController::class, 'updateStokGudangKeluar'])->name('admin.gudang.stok-keluar.update');

      Route::get('/toko/gudang/produk/stok-keluar/delete/{id}', [
         AdminController::class,
         'deleteStokGudangKeluar'
      ])->name('admin.gudang.stok-keluar.destroy');



      // produk toko
      Route::get('/toko/produk', [AdminController::class, 'produkToko'])->name('admin.produk');
      Route::get('/toko/produk/{id}/varian', [AdminController::class, 'getVarian']);
      Route::post(
         '/admin/produk/varian',
         [AdminController::class, 'storeOrUpdate']
      )->name('admin.produk.varian.store');

      Route::get(
         '/toko/produk/{produkId}/toggle-display',
         [AdminController::class, 'toggleDisplayProdukToko']
      )->name('admin.toko.produk.toggle-display');

      Route::get('/toko/produk/update-harga/{id}', [AdminController::class, 'editHargaProdukToko'])->name('admin.produk-toko.edit-harga');

      Route::get(
         'toko/produk/log',
         [AdminController::class, 'logToko']
      )->name('admin.produk-toko.log');

      Route::get(
         'toko/produk/log/detail/{produkVarianId}',
         [AdminController::class, 'detailLogToko']
      )->name('admin.produk-toko.detail-log');


      // stok toko masuk
      Route::post('/toko/produk/stok/store', [AdminController::class, 'storeStokToko'])->name('admin.stok.store');
      Route::put('/toko/produk/stok/update/{id}', [AdminController::class, 'updateStokToko'])->name('admin.stok.update');
      Route::get('/toko/produk/stok/delete/{id}', [AdminController::class, 'deleteStokToko'])->name('admin.stok.delete');
      Route::get('/toko/produk/stok-masuk', [AdminController::class, 'stok'])->name('admin.stok');

      // stok toko keluar
      Route::get(
         'toko/produk/stok-keluar/add',
         [AdminController::class, 'addStokKeluar']
      )->name('admin.stok-keluar.add');
      Route::post('/toko/produk/stok-keluar/store', [AdminController::class, 'storeStokTokoKeluar'])->name('admin.stok-keluar.store');
      Route::get('/toko/produk/stok-keluar/update/{id}', [AdminController::class, 'updateStokTokoKeluar'])->name('admin.stok-keluar.update');
      Route::get('/toko/produk/stok-keluar/delete/{id}', [AdminController::class, 'deleteStokTokoKeluar'])->name('admin.stok-keluar.delete');
      Route::get('/toko/produk/stok-keluar', [AdminController::class, 'stokTokoKeluar'])->name('admin.stok-keluar');

      // shift
      Route::get('/toko/shift', [AdminController::class, 'shift'])->name('admin.shift');
      Route::post('/toko/{id}/shift', [AdminController::class, 'storeShift'])->name('admin.shift.store');
      Route::put('/toko/shift/update/{id}', [AdminController::class, 'updateShift'])->name('admin.shift.update');
      Route::get('/toko/shift/delete/{id}', [AdminController::class, 'deleteShift'])->name('admin.shift.delete');

      // kasir
      Route::get('/toko/kasir', [AdminController::class, 'kasir'])->name('admin.kasir');
      Route::post('/toko/kasir/store', [AdminController::class, 'storeKasir'])->name('admin.kasir.store');
      Route::put('/toko/kasir/update/{id}', [AdminController::class, 'updateKasir'])->name('admin.kasir.update');
      Route::get('/toko/kasir/delete/{id}', [AdminController::class, 'deleteKasir'])->name('admin.kasir.delete');

      // pengeluaran
      Route::get('/toko/pengeluaran', [AdminController::class, 'pengeluaran'])->name('admin.pengeluaran');
      Route::get('/toko/pengeluaran/add', [AdminController::class, 'addPengeluaran'])->name('admin.pengeluaran.add');
      Route::post('/toko/pengeluaran/store', [AdminController::class, 'storePengeluaran'])->name('admin.pengeluaran.store');
      Route::get('/toko/pengeluaran/edit/{id}', [AdminController::class, 'editPengeluaran'])->name('admin.pengeluaran.edit');
      Route::get('/toko/pengeluaran/update/{id}', [AdminController::class, 'updatePengeluaran'])->name('admin.pengeluaran.update');
      Route::get('/toko/pengeluaran/delete/{id}', [AdminController::class, 'deletePengeluaran'])->name('admin.pengeluaran.delete');

      // Pendapatan
      Route::get('/toko/pendapatan', [AdminController::class, 'pendapatan'])->name('admin.pendapatan');
      Route::get('/toko/pendapatan/export', [AdminController::class, 'exportPendapatan'])
         ->name('admin.pendapatan.export');


      // data transaksi
      Route::get('/toko/data-transaksi', [AdminController::class, 'dataTransaksi'])->name('admin.data-transaksi');
      Route::get('/toko/data-transaksi/detail/{id}', [AdminController::class, 'detailTransaksi'])->name('admin.detailTransaksi');
      Route::get('/toko/data-transaksi/export', [AdminController::class, 'exportDataTransaksi'])
         ->name('admin.dataTransaksi.export');

      // pembatalan dan retur
      Route::get('/toko/pembatalan-dan-retur', [AdminController::class, 'pembatalanDanRetur'])->name('admin.pembatalanRetur');

      // catatan
      route::get('toko/catatan', [AdminController::class, 'catatan'])->name('admin.catatan');
      route::get('/toko/catatan/toggle-status/{id}', [AdminController::class, 'toggleCatatan'])->name('admin.catatan.toggle');

      // Printer
      // Route::get('/toko/printer', [adminController::class, 'printerIndex'])->name('admin.printer');
      // Route::post('/toko/printer/store', [adminController::class, 'printerStore'])->name('admin.printer.store');
      // Route::get('/toko/printer/delete/{id}', [adminController::class, 'printerDelete'])->name('admin.printer.delete');
      // Route::get('/toko/printer/update/{id}', [adminController::class, 'printerUpdate'])->name('admin.printer.edit');

      //Status validasi retur
      Route::put('/toko/transaksi/validasi-pembatalan-dan-retur/{id}', [AdminController::class, 'validasi'])->name('admin.validasi');
   });

Route::middleware(['auth', 'role:superadmin'])->group(function () {
   Route::get('/home', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
   Route::get('/pengeluaran', [SuperAdminController::class, 'pengeluaran'])->name('superadmin.pengeluaran');

   // admin management
   Route::get('/admin', [SuperAdminController::class, 'adminIndex'])->name('superadmin.admin');
   Route::post('/admin', [SuperAdminController::class, 'adminStore'])->name('superadmin.admin.store');
   Route::get('/admin/disable/{id}', [SuperAdminController::class, 'adminStatus'])->name('superadmin.admin.status');
   Route::get('/admin/edit/{id}', [SuperAdminController::class, 'adminEdit'])->name('superadmin.admin.edit');
   Route::get('/admin/update/{id}', [SuperAdminController::class, 'adminUpdate'])->name('superadmin.admin.update');

   // store management
   Route::get('/daftar-toko', [SuperAdminController::class, 'tokoIndex'])->name('superadmin.toko');
   Route::get('/daftar-toko/add', [SuperAdminController::class, 'addToko'])->name('superadmin.toko.add');
   Route::post('/daftar-toko/store', [SuperAdminController::class, 'storeToko'])->name('superadmin.toko.store');
   Route::get('/daftar-toko/edit/{id}', [SuperAdminController::class, 'editToko'])->name('superadmin.toko.edit');
   Route::get('/daftar-toko/update/{id}', [SuperAdminController::class, 'updateToko'])->name('superadmin.toko.update');

   // gudang
   Route::get('/gudang', [SuperAdminController::class, 'gudang'])->name('superadmin.gudang');
   Route::post('/gudang/store', [SuperAdminController::class, 'gudangStore'])->name('superadmin.gudang.store');
   Route::get('/gudang/edit/{id}', [SuperAdminController::class, 'gudangEdit'])->name('superadmin.gudang.edit');
   Route::get('/gudang/update/{id}', [SuperAdminController::class, 'gudangUpdate'])->name('superadmin.gudang.update');

   // barang masuk
   Route::get('/gudang/barang-masuk', [SuperAdminController::class, 'barangMasuk'])->name('superadmin.gudang.barang-masuk');

   // barang keluar
   Route::get('/gudang/barang-keluar', [SuperAdminController::class, 'barangKeluar'])->name('superadmin.gudang.barang-keluar');

   // log gudang
   Route::get(
      '/log/gudang',
      [SuperAdminController::class, 'logGudang']
   )->name('superadmin.log.gudang');
   Route::get(
      '/log/gudang/detail/{produkId}',
      [SuperAdminController::class, 'detailLogGudang']
   )->name('superadmin.log.gudang.detail-log');

   // log toko
   Route::get(
      '/log/toko',
      [SuperAdminController::class, 'logToko']
   )->name('superadmin.log.toko');
   Route::get(
      '/log/toko/{tenantId}/detail/{produkVarianId}',
      [SuperAdminController::class, 'detailLogToko']
   )->name('superadmin.log.toko.detail-log');

   // pendapatan
   Route::get('/pendapatan', [SuperAdminController::class, 'pendapatan'])->name('superadmin.pendapatan');
});

Route::middleware(['auth', 'role:kasir'])->group(function () {
   Route::get('/', [KasirController::class, 'index'])->name('kasir.dashboard');
   Route::get('/transaksi', [KasirController::class, 'transaksi'])->name('kasir.transaksi');

   Route::post('/transaksi/post', [TransaksiController::class, 'postTransaksi'])->name('transaksi.post');
   Route::get('/transaksi/riwayat-transaksi', [KasirController::class, 'historiTransaksi'])->name('kasir.historiTransaksi');
   Route::get('/transaksi/detail-transaksi/{id}', [KasirController::class, 'detailTransaksi'])->name('kasir.detailTransaksi');
   Route::get('/transaksi/data-transaksi/export', [KasirController::class, 'exportHistoriTransaksi'])
      ->name('kasir.historiTransaksi.export');

    // print struk transaksi di HP
   Route::get('/print/struk/base64/{transaksiId}', [TransaksiController::class, 'strukBase64']);

   Route::put('/detailTransaksi/updateProdukTransaksi/{id}', [TransaksiController::class, 'updateProdukTransaksi'])->name('kasir.updateProdukTransaksi');
   Route::put('/detailTransaksi/hapusProdukTransaksi/{id}', [TransaksiController::class, 'hapusProdukTransaksi'])->name('kasir.hapusProdukTransaksi');
   Route::get('/retur-produk', [KasirController::class, 'returProduk'])->name('kasir.returProduk');
   Route::post('/ajax/update-status-transaksi/{id}', [AjaxController::class, 'updateStatusTransaksi'])
      ->name('ajax.updateStatusTransaksi');

   // histori stok masuk
   Route::get('/histori-stok-masuk', [KasirController::class, 'historiStokMasuk'])->name('kasir.historiStokMasuk');

   // pengeluaran toko
   Route::get('/pengeluaran-toko', [KasirController::class, 'pengeluaranToko'])->name('kasir.pengeluaranToko');
   Route::get('/pengeluaran-toko/add', [KasirController::class, 'addPengeluaranToko'])->name('kasir.pengeluaranToko.add');
   Route::post('/pengeluaran-toko/store', [KasirController::class, 'storePengeluaranToko'])->name('kasir.pengeluaranToko.store');
   Route::get('/pengeluaran-toko/edit/{id}', [KasirController::class, 'editPengeluaranToko'])->name('kasir.pengeluaranToko.edit');
   Route::get('/pengeluaran-toko/update/{id}', [KasirController::class, 'updatePengeluaranToko'])->name('kasir.pengeluaranToko.update');

   // pelanggan dan penukaran poin
   Route::get('/pelanggan', [KasirController::class, 'pelanggan'])->name('kasir.penukaranPoinPelanggan');
   Route::post('/pelanggan/store', [KasirController::class, 'storePelanggan'])->name('kasir.pelanggan.store');
   Route::get('/pelanggan/update/{id}', [KasirController::class, 'updatePelanggan'])->name('kasir.pelanggan.update');
   Route::put('/prosesPenukaranPoin/{id}', [KasirController::class, 'prosesPenukaranPoin'])->name('kasir.prosesPenukaranPoin');

   // AJAX
   Route::get('/ajax/cekStokProdukTransaksi/{id}', [AjaxController::class, 'cekStokProdukTransaksi'])
      ->name('ajax.cekStokProdukTransaksi');
   Route::get('/ajax/getProduk', [AjaxController::class, 'getProduk'])->name('ajax.getProduk');
   Route::get('/ajax/filterTransaksi', [AjaxController::class, 'filterTransaksi'])->name('ajax.filterTransaksi');
   Route::get('/ajax/filterTransaksiProduk', [AjaxController::class, 'filterTransaksiProduk'])->name('ajax.filterTransaksiProduk');
   Route::get('/ajax/transaksi/export-excel', [AjaxController::class, 'exportTransaksiExcel'])
      ->name('transaksi.exportExcel');

   Route::get('/pelanggan/data', [AjaxController::class, 'getDataPelanggan'])
      ->name('ajax.pelanggan.data');

   Route::get('/ajax/getDetailTransaksi/{id}', [AjaxController::class, 'getDetailTransaksi'])->name('ajax.getDetailTransaksi');

   // catatan
   route::get('/catatan', [KasirController::class, 'catatan'])->name('kasir.catatan');
   route::post('/catatan/store', [KasirController::class, 'catatanStore'])->name('kasir.catatan.store');
   route::put('/catatan/update/{id}', [KasirController::class, 'catatanUpdate'])->name('kasir.catatan.update');

   route::get('/cetak-rekap/{ShiftId}', [KasirController::class, 'cetakRekap'])->name('kasir.cetak-rekap');
   
   Route::get(
      '/cetak-rekap-rawbt/{shiftId}',
      [KasirController::class, 'cetakRekapRawBT']
   )->name('rekap.rawbt');
});

Route::middleware('guest')->group(function () {
   Route::get('/login', function () {
      return view('auth.login');
   })->name('login');
});
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
