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
   { {
         Schema::create('produk_varian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_toko_id')->constrained('produk_toko')->cascadeOnDelete();
            $table->string('nama');
            $table->decimal('harga_jual', 15, 2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // optional tapi recommended
            $table->index(['produk_toko_id', 'is_active']);
         });
      }
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('produk_varian');
   }
};
