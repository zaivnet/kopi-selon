<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
    * Run the migrations.
    */
   public function up(): void
   {
      Schema::create('stok_toko_keluar', function (Blueprint $table) {
         $table->id();
         $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
         $table->foreignId('kasir_id')->constrained('users')->restrictOnDelete();
         $table->integer('produk_varian_id');
         $table->integer('jumlah_retur');
         $table->integer('harga_produk');
         $table->string('keterangan');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('stok_toko_keluar');
   }
};
