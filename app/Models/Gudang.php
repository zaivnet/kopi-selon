<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
   use HasFactory;

   protected $table = 'gudang';
   protected $fillable = [
      'name',
   ];

   public function tenants()
   {
      return $this->belongsToMany(
         Tenant::class,
         'gudang_has_tenants',
         'gudang_id',
         'tenant_id'
      );
   }

   public function produk()
   {
      return $this->hasMany(Produk::class);
   }

   public function stokMasuk()
   {
      return $this->hasMany(StokGudangMasuk::class);
   }

   public function stokKeluar()
   {
      return $this->hasMany(StokGudangKeluar::class);
   }

   public function kategori()
   {
      return $this->belongsToMany(Kategori::class, 'gudang_kategori');
   }
}
