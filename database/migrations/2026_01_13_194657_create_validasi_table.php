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
      Schema::create('validasi', function (Blueprint $table) {
         $table->id();
         $table->unsignedBigInteger('transaksi_id');
         $table->unsignedBigInteger('detail_transaksi_id');
         $table->unsignedBigInteger('admin_id')->nullable();
         $table->enum('type', ['pembatalan', 'retur']);
         $table->integer('jumlah_item');
         $table->decimal('harga_item', 15, 2);
         $table->enum('status_validasi', ['pending', 'diterima', 'ditolak']);
         $table->text('keterangan')->nullable();
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('validasi_retur');
   }
};
