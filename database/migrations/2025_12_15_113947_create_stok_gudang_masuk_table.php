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
      Schema::create('stok_gudang_masuk', function (Blueprint $table) {
         $table->id();
         $table->foreignId('gudang_id')
            ->constrained('gudang')
            ->cascadeOnDelete();

         $table->foreignId('produk_id')
            ->constrained('produk')
            ->cascadeOnDelete();

         $table->foreignId('user_id')
            ->constrained('users')
            ->restrictOnDelete();

         $table->integer('qty_masuk');
         $table->integer('harga_pembelian');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('stok_gudang_masuk');
   }
};
