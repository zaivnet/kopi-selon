<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukVarian extends Model
{
   use HasFactory;
   protected $table = 'produk_varian';

   protected $fillable = [
      'produk_toko_id',
      'nama',
      'harga_jual',
      'is_active',
   ];

   /**
    * Relasi ke produk toko
    */
   public function produkToko()
   {
      return $this->belongsTo(ProdukToko::class, 'produk_toko_id');
   }
}
