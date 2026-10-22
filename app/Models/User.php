<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

   /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
   protected $fillable = [
      'name',
      'username',
      'email',
      'password',
      'isActive',
      'role',
   ];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
   protected $hidden = [
      'password',
      'remember_token',
   ];

   /**
    * The attributes that should be cast.
    *
    * @var array<string, string>
    */
   // protected $casts = [
   //    'email_verified_at' => 'datetime',
   // ];

   public function tenants()
   {
      return $this->belongsToMany(Tenant::class, 'user_has_tenants', 'user_id', 'tenant_id');
   }

   public function stok()
   {
      return $this->hasMany(Stok::class, 'admin_id');
   }

   public function pengeluaran()
   {
      return $this->hasMany(Pengeluaran::class, 'user_id');
   }

   public function stokGudangMasuk()
   {
      return $this->hasMany(StokGudangMasuk::class);
   }

   public function stokGudangKeluar()
   {
      return $this->hasMany(StokGudangKeluar::class);
   }

   public function stokTokoKeluar()
   {
      return $this->hasMany(StokTokoKeluar::class, 'kasir_id');
   }

   public function catatanSebagaiKasir()
   {
      return $this->hasMany(Catatan::class, 'kasir_id');
   }

   // Catatan yang ditangani sebagai admin
   public function catatanSebagaiAdmin()
   {
      return $this->hasMany(Catatan::class, 'admin_id');
   }

   public function transaksis()
   {
      return $this->hasMany(Transaksi::class, 'kasir_id');
   }

   public function isSuperadmin()
   {
      return $this->role === 'superadmin';
   }

   public function isAdmin()
   {
      return $this->role === 'admin';
   }

   public function isKasir()
   {
      return $this->role === 'kasir';
   }

   public function isActive()
   {
      return $this->isActive;
   }
}
