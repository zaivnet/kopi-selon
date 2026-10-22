<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
   use HasFactory;

   protected $table = 'pengeluaran';
   protected $fillable = [
      'tenant_id',
      'deskripsi',
      'jumlah',
      'keterangan',
      'user_id',
   ];


   public function tenant()
   {
      return $this->belongsTo(Tenant::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class, 'user_id');
   }
}
