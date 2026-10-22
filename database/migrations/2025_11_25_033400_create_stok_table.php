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
      Schema::create('stok', function (Blueprint $table) {
         $table->id();
         $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
         $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
         $table->string('stok_masuk');
         $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('stok');
   }
};
