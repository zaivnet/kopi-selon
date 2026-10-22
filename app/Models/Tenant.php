<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
   use HasFactory;

   protected $fillable = [
      'name',
      'slug',
      'instagram',
      'tiktok',
      'address',
      'logo',
      'isActive',
   ];

   public function users()
   {
      return $this->belongsToMany(User::class, 'user_has_tenants', 'tenant_id', 'user_id');
   }

   public function kategori()
   {
      return $this->hasMany(Kategori::class);
   }

   public function gudangs()
   {
      return $this->belongsToMany(
         Gudang::class,
         'gudang_has_tenants',
         'tenant_id',   // FK ke tabel tenants
         'gudang_id',    // FK ke tabel gudangs
      );
   }

   public function produk()
   {
      return $this->belongsToMany(Produk::class, 'produk_toko')
         ->withPivot('stok', 'display');
   }

   public function stok()
   {
      return $this->hasMany(Stok::class);
   }

   public function shift()
   {
      return $this->hasMany(Shift::class);
   }

   public function pengeluaran()
   {
      return $this->hasMany(Pengeluaran::class);
   }

   public function stokTokoKeluar()
   {
      return $this->hasMany(StokTokoKeluar::class);
   }

   public function catatan()
   {
      return $this->hasMany(Catatan::class);
   }
}
