<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\StokGudangMasuk;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StokGudangMasukExport implements FromView, ShouldAutoSize
{
   protected $tenant;
   protected $bulan;
   protected $produk;

   public function __construct($tenant, $bulan = null, $produk = null)
   {
      $this->tenant = $tenant;
      $this->bulan  = $bulan;
      $this->produk = $produk;
   }

   public function view(): View
   {
      $bulanAktif = $this->bulan
         ? Carbon::parse($this->bulan)
         : now();

      $baseQuery = StokGudangMasuk::whereHas(
         'gudang.tenants',
         fn($q) => $q->where('tenants.id', $this->tenant->id)
      )
         ->whereMonth('created_at', $bulanAktif->month)
         ->whereYear('created_at', $bulanAktif->year)
         ->when($this->produk, function ($q) {
            $q->whereHas('produk', function ($p) {
               $p->where('nama_produk', 'like', '%' . $this->produk . '%');
            });
         })
         ->with(['produk', 'gudang', 'admin'])
         ->orderBy('created_at', 'asc');

      $data = $baseQuery->get();

      $total = (clone $baseQuery)
         ->selectRaw('
         COALESCE(SUM(qty_masuk), 0) as total_qty_masuk,
         COALESCE(SUM(qty_masuk * harga_pembelian), 0) as total_harga_pembelian
      ')
         ->first();

      $gudangNames = $data->pluck('gudang.name')->unique()->values();

      return view('exports.stok-gudang-masuk', [
         'data'  => $data,
         'total' => $total,
         'gudangNames' => $gudangNames,
      ]);
   }
}
