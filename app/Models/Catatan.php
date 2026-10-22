<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catatan extends Model
{
   use HasFactory;
   protected $table = 'catatan';
   protected $fillable = [
      'tenant_id',
      'catatan',
      'kasir_id',
      'admin_id'
   ];

   public function tenant()
   {
      return $this->belongsTo(Tenant::class);
   }

   public function kasir()
   {
      return $this->belongsTo(User::class, 'kasir_id');
   }

   public function admin()
   {
      return $this->belongsTo(User::class, 'admin_id');
   }
}
