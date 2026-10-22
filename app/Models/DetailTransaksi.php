<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
   use HasFactory;

   protected $fillable = [
      'transaksi_id',
      'produk_varian_id',
      'jumlah',
      'harga_satuan',
      'total_harga_produk',
      // tambahkan atribut lain sesuai kebutuhan
   ];

   public function produkVarian()
   {
      return $this->belongsTo(ProdukVarian::class);
   }
}
