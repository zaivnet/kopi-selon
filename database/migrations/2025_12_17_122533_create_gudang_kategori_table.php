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
      Schema::create('gudang_kategori', function (Blueprint $table) {
         $table->id();
         $table->foreignId('gudang_id')->constrained('gudang')->cascadeOnDelete();
         $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnDelete();
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('gudang_kategori');
   }
};
