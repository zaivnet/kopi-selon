<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
   use HasFactory;
   protected $table = 'produk';

   protected $fillable = [
      'gudang_id',
      'kode',
      'nama_produk',
      'kategori_id',
      'stok_gudang',
   ];

   public function gudang()
   {
      return $this->belongsTo(Gudang::class);
   }

   public function tenant()
   {
      return $this->belongsToMany(Tenant::class, 'produk_toko', 'produk_id', 'tenant_id')
         ->withPivot(['stok', 'display']);
   }

   public function kategori()
   {
      return $this->belongsTo(Kategori::class);
   }

   public function stok()
   {
      return $this->hasMany(Stok::class);
   }

   public function stokMasuk()
   {
      return $this->hasMany(StokGudangMasuk::class);
   }

   public function stokKeluar()
   {
      return $this->hasMany(StokGudangKeluar::class);
   }

   public function stokTokoKeluar()
   {
      return $this->hasMany(StokTokoKeluar::class, 'produk_toko_id');
   }

   public function produkToko()
   {
      return $this->hasMany(ProdukToko::class, 'produk_id');
   }
}
