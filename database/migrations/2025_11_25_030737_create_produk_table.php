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
      Schema::create('produk', function (Blueprint $table) {
         $table->id();
         $table->string('kode')->nullable();
         $table->foreignId('gudang_id')->constrained('gudang')->onDelete('cascade');
         $table->string('nama_produk');
         $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
         $table->string('stok_gudang')->default(0);
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('produk');
   }
};
