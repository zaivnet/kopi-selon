<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
   use HasFactory;

   protected $fillable = [
      'tenant_id',
      'kode_transaksi',
      'kasir_id',
      'pelanggan_id',
      'nama_pelanggan',
      'total_item',
      'total_harga',
      'bayar',
      'kembalian',
      'metode_pembayaran',
      'status',
      // tambahkan atribut lain sesuai kebutuhan
   ];

   public function kasir()
   {
      return $this->belongsTo(User::class, 'kasir_id');
   }
}
