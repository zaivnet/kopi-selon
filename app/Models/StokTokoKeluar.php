<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokTokoKeluar extends Model
{
   use HasFactory;

   protected $table = 'stok_toko_keluar';

   protected $fillable = [
      'tenant_id',
      'kasir_id',
      'produk_varian_id',
      'jumlah_retur',
      'harga_produk',
      'keterangan',
   ];

   // =========================
   // RELATIONS
   // =========================

   public function tenant()
   {
      return $this->belongsTo(Tenant::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class, 'kasir_id');
   }

   public function produkVarian()
   {
      return $this->belongsTo(
         ProdukVarian::class,
         'produk_varian_id',
      );
   }
}
