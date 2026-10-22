<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokGudangMasuk extends Model
{
   use HasFactory;

   protected $table = 'stok_gudang_masuk';

   protected $fillable = [
      'gudang_id',
      'produk_id',
      'user_id',
      'qty_masuk',
      'harga_pembelian',
   ];

   public function gudang()
   {
      return $this->belongsTo(Gudang::class, 'gudang_id');
   }

   public function produk()
   {
      return $this->belongsTo(Produk::class);
   }

   public function admin()
   {
      return $this->belongsTo(User::class, 'user_id');
   }
}
