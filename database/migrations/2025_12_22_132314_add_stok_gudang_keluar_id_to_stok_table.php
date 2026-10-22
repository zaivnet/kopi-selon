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
      Schema::table('stok', function (Blueprint $table) {
         $table->foreignId('stok_gudang_keluar_id')
            ->after('produk_id')      // bebas mau di mana
            ->nullable()              // PENTING (data lama)
            ->constrained('stok_gudang_keluar')
            ->cascadeOnDelete();
      });
   }

   public function down(): void
   {
      Schema::table('stok', function (Blueprint $table) {
         $table->dropForeign(['stok_gudang_keluar_id']);
         $table->dropColumn('stok_gudang_keluar_id');
      });
   }
};
