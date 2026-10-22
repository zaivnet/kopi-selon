<?php

namespace App\Models;

use App\Models\ProdukVarian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukToko extends Model
{
   protected $table = 'produk_toko';

   protected $fillable = [
      'tenant_id',
      'produk_id',
      'stok',
      'display'
   ];

   // =====================
   // RELASI
   // =====================

   public function tenant()
   {
      return $this->belongsTo(Tenant::class);
   }

   public function produk()
   {
      return $this->belongsTo(Produk::class);
   }

   public function stokKeluar()
   {
      return $this->hasMany(StokTokoKeluar::class, 'produk_toko_id');
   }

   public function varians()
   {
      return $this->hasMany(ProdukVarian::class, 'produk_toko_id');
   }

   /**
    * Varian aktif saja (helper)
    */
   public function variansAktif()
   {
      return $this->hasMany(ProdukVarian::class, 'produk_toko_id')
         ->where('is_active', true);
   }
}
