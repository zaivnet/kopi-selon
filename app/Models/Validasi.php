<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validasi extends Model
{
   use HasFactory;

   protected $table = 'validasi';

   protected $fillable = [
      'transaksi_id',
      'detail_transaksi_id',
      'admin_id',
      'type',
      'jumlah_item',
      'harga_item',
      'status_validasi',
      'keterangan',
   ];

   public function detailTransaksi()
   {
      return $this->belongsTo(DetailTransaksi::class);
   }

   public function transaksi()
   {
      return $this->belongsTo(Transaksi::class);
   }

   public function admin()
   {
      return $this->belongsTo(User::class, 'admin_id');
   }
}
