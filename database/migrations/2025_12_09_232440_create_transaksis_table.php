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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('kode_transaksi')->unique();
            $table->unsignedBigInteger('kasir_id');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->string('nama_pelanggan')->nullable();
            $table->integer('total_item');
            $table->decimal('total_harga', 15, 2);
            $table->decimal('bayar', 15, 2);
            $table->decimal('kembalian', 15, 2);
            $table->enum('metode_pembayaran', ['offline', 'online']);
            $table->enum('status', ['selesai', 'retur'])->default('selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
