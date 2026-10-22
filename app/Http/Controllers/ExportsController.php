<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Produk;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokGudangMasukExport;
use App\Exports\StokGudangKeluarExport;

class ExportsController extends Controller
{

   public function exportStokGudangMasuk(Request $request, $id)
   {
      $tenant = Tenant::with('gudangs')->findOrFail($id);

      // =========================
      // BULAN AKTIF (DEFAULT: BULAN INI)
      // =========================
      $bulanAktif = $request->filled('bulan')
         ? Carbon::parse($request->bulan)
         : now();

      // =========================
      // NAMA GUDANG
      // =========================
      if ($tenant->gudangs->count() === 1) {
         $gudangNama = $tenant->gudangs->first()->name;
      } else {
         $gudangNama = 'multi-gudang';
      }

      // =========================
      // PRODUK (OPSIONAL)
      // =========================
      $produkNama = null;
      if ($request->filled('produk')) {
         $produk = Produk::find($request->produk);
         $produkNama = $produk?->nama_produk;
      }

      // =========================
      // NAMA FILE DINAMIS
      // =========================
      $filename = 'stok-gudang-masuk';
      $filename .= '-' . str()->slug($gudangNama);
      $filename .= '-' . $bulanAktif->format('Y-m');

      if ($produkNama) {
         $filename .= '-' . str()->slug($produkNama);
      }

      $filename .= '.xlsx';

      return Excel::download(
         new StokGudangMasukExport(
            $tenant,
            $request->bulan,
            $request->produk
         ),
         $filename
      );
   }

   public function exportStokGudangKeluar(Request $request, $id)
   {
      $tenant = Tenant::with('gudangs')->findOrFail($id);

      // =========================
      // BULAN AKTIF
      // =========================
      $bulanAktif = $request->filled('bulan')
         ? Carbon::parse($request->bulan)
         : now();

      // =========================
      // NAMA GUDANG
      // =========================
      if ($tenant->gudangs->count() === 1) {
         $gudangNama = $tenant->gudangs->first()->name;
      } else {
         $gudangNama = 'multi-gudang';
      }

      // =========================
      // PRODUK (OPSIONAL)
      // =========================
      $produkNama = null;
      if ($request->filled('produk')) {
         $produk = Produk::find($request->produk);
         $produkNama = $produk?->nama_produk;
      }

      // =========================
      // NAMA FILE
      // =========================
      $filename = 'stok-gudang-keluar';
      $filename .= '-' . str()->slug($gudangNama);
      $filename .= '-' . $bulanAktif->format('Y-m');

      if ($produkNama) {
         $filename .= '-' . str()->slug($produkNama);
      }

      $filename .= '.xlsx';

      return Excel::download(
         new StokGudangKeluarExport(
            $tenant,
            $request->bulan,
            $request->produk
         ),
         $filename
      );
   }
}
