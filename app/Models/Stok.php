<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
   use HasFactory;
   protected $table = 'stok';

   protected $fillable = [
      'tenant_id',
      'produk_id',
      'stok_masuk',
      'stok_gudang_keluar_id',
      'admin_id',
   ];

   public function stokGudangKeluar()
   {
      return $this->belongsTo(StokGudangKeluar::class, 'stok_gudang_keluar_id');
   }

   public function produk()
   {
      return $this->belongsTo(Produk::class);
   }

   public function tenant()
   {
      return $this->belongsTo(Tenant::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class, 'admin_id');
   }
}
