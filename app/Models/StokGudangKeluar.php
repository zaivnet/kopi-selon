<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokGudangKeluar extends Model
{
   use HasFactory;

   protected $table = 'stok_gudang_keluar';
   protected $fillable = [
      'gudang_id',
      'produk_id',
      'qty_keluar',
      'keterangan',
      'user_id'
   ];

   public function gudang()
   {
      return $this->belongsTo(Gudang::class, 'gudang_id');
   }

   public function produk()
   {
      return $this->belongsTo(Produk::class, 'produk_id');
   }

   public function admin()
   {
      return $this->belongsTo(User::class, 'user_id');
   }

   public function stokToko()
   {
      return $this->hasOne(Stok::class, 'stok_gudang_keluar_id');
   }
}
