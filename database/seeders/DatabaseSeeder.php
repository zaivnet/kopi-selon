<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
   /**
    * Seed the application's database.
    */
   public function run(): void
   {
      // user seeder
      DB::table('users')->insert([
         'id' => 1,
         'name' => 'DEVELOPER',
         'username' => 'devteam',
         'email' => 'devteam@example.com',
         'password' => Hash::make('selondevteam'),
         'isActive' => '1',
         'role' => 'superadmin',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Super Admin',
         'username' => 'superadmin',
         'email' => 'superadmin@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'superadmin',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Owner',
         'username' => 'owner',
         'email' => 'owner@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'superadmin',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Admin',
         'username' => 'admin',
         'email' => 'admin@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'admin',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Admin2',
         'username' => 'admin2',
         'email' => 'admin2@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'admin',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Kasir',
         'username' => 'kasir',
         'email' => 'kasir@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'kasir',
         'created_at' => Carbon::now(),
      ]);

      DB::table('users')->insert([
         'name' => 'Kasir2',
         'username' => 'kasir2',
         'email' => 'kasir2@example.com',
         'password' => Hash::make('123456'),
         'isActive' => '1',
         'role' => 'kasir',
         'created_at' => Carbon::now(),
      ]);

      // gudang seeder
      DB::table('gudang')->insert([
         'name' => 'Gudang Kopi Selon',
         'created_at' => Carbon::now(),
      ]);

      // tenant seeder
      DB::table('tenants')->insert([
         'name' => 'Kopi Selon',
         'instagram' => '@kopi_selon',
         'tiktok' => '@kopi_selon',
         'address' => 'Jl. Contoh Alamat No. 123, Kota, Provinsi',
         'isActive' => '1',
         'created_at' => Carbon::now(),
      ]);

      DB::table('tenants')->insert([
         'name' => 'Kopi Selon 2',
         'instagram' => '@kopi_selon',
         'tiktok' => '@kopi_selon',
         'address' => 'Jl. Contoh Alamat No. 123, Kota, Provinsi',
         'isActive' => '1',
         'created_at' => Carbon::now(),
      ]);

      DB::table('gudang_has_tenants')->insert([
         'gudang_id' => 1,
         'tenant_id' => 1,
         'created_at' => Carbon::now(),
      ]);
      DB::table('gudang_has_tenants')->insert([
         'gudang_id' => 1,
         'tenant_id' => 2,
         'created_at' => Carbon::now(),
      ]);

      // user_has_tenants seeder
      DB::table('user_has_tenants')->insert([
         'user_id' => 4, //admin
         'tenant_id' => 1, // Kopi Selon
         'created_at' => Carbon::now(),
      ]);
      DB::table('user_has_tenants')->insert([
         'user_id' => 4, //admin
         'tenant_id' => 2, // Kopi Selon 2
         'created_at' => Carbon::now(),
      ]);
      DB::table('user_has_tenants')->insert([
         'user_id' => 5, //admin
         'tenant_id' => 2, // Kopi Selon 2
         'created_at' => Carbon::now(),
      ]);
      DB::table('user_has_tenants')->insert([
         'user_id' => 6, //kasir
         'tenant_id' => 1, // Kopi Selon 1
         'created_at' => Carbon::now(),
      ]);
      DB::table('user_has_tenants')->insert([
         'user_id' => 7, //kasir
         'tenant_id' => 2, // Kopi Selon 2
         'created_at' => Carbon::now(),
      ]);

      DB::table('kategori')->insert([
         'nama_kategori' => 'Minuman',
         'created_at' => Carbon::now(),
      ]);
      DB::table('kategori')->insert([
         'nama_kategori' => 'Makanan',
         'created_at' => Carbon::now(),
      ]);

      DB::table('gudang_kategori')->insert([
         'gudang_id' => 1,
         'kategori_id' => 1,
         'created_at' => Carbon::now(),
      ]);

      DB::table('gudang_kategori')->insert([
         'gudang_id' => 1,
         'kategori_id' => 2,
         'created_at' => Carbon::now(),
      ]);

      // Produk
      // DB::table('produk')->insert([
      //    'gudang_id' => 1,
      //    'kode' => 'BRG0001',
      //    'nama_produk' => 'Nutrisari',
      //    'kategori_id' => 1,
      //    'stok_gudang' => '200',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk')->insert([
      //    'gudang_id' => 1,
      //    'kode' => 'BRG0002',
      //    'nama_produk' => 'Kentang',
      //    'kategori_id' => 1,
      //    'stok_gudang' => '200',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk')->insert([
      //    'gudang_id' => 1,
      //    'kode' => 'BRG0002',
      //    'nama_produk' => 'Kentang Goreng',
      //    'kategori_id' => 2,
      //    'stok_gudang' => '500',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_masuk')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 1,
      //    'user_id' => 3,
      //    'qty_masuk' => 300,
      //    'harga_pembelian' => 380000,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_masuk')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 2,
      //    'user_id' => 3,
      //    'qty_masuk' => 200,
      //    'harga_pembelian' => 140000,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_masuk')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 2,
      //    'user_id' => 3,
      //    'qty_masuk' => 700,
      //    'harga_pembelian' => 450000,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_keluar')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 1,
      //    'user_id' => 3,
      //    'qty_keluar' => 100,
      //    'keterangan' => 'Diinput ke toko A',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_keluar')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 2,
      //    'user_id' => 3,
      //    'qty_keluar' => 100,
      //    'keterangan' => 'Diinput ke toko A',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_gudang_keluar')->insert([
      //    'gudang_id' => 1,
      //    'produk_id' => 2,
      //    'user_id' => 3,
      //    'qty_keluar' => 100,
      //    'keterangan' => 'Diinput ke toko B',
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk_toko')->insert([
      //    'produk_id' => 1,
      //    'tenant_id' => 1,
      //    'stok' => 100,
      //    'display' => true,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk_toko')->insert([
      //    'produk_id' => 2,
      //    'tenant_id' => 1,
      //    'harga_jual' => 10000,
      //    'stok' => 100,
      //    'display' => true,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk_toko')->insert([
      //    'produk_id' => 1,
      //    'tenant_id' => 2,
      //    'harga_jual' => 20000,
      //    'stok' => 100,
      //    'display' => true,
      //    'created_at' => Carbon::now(),
      // ]);

      // // stok
      // DB::table('stok')->insert([
      //    'tenant_id' => 1,
      //    'stok_gudang_keluar_id' => 1,
      //    'produk_id' => 1,
      //    'stok_masuk' => '100',
      //    'admin_id' => 3,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok')->insert([
      //    'tenant_id' => 1,
      //    'stok_gudang_keluar_id' => 2,
      //    'produk_id' => 2,
      //    'stok_masuk' => '100',
      //    'admin_id' => 3,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok')->insert([
      //    'tenant_id' => 2,
      //    'stok_gudang_keluar_id' => 3,
      //    'produk_id' => 1,
      //    'stok_masuk' => '100',
      //    'admin_id' => 3,
      //    'created_at' => Carbon::now(),
      // ]);
      // DB::table('produk_varian')->insert([
      //    'produk_toko_id' => 1,
      //    'nama' => 'Nutrisari Susu Panas',
      //    'harga_jual' => 5000,
      //    'is_active' => true,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('produk_varian')->insert([
      //    'produk_toko_id' => 1,
      //    'nama' => 'Nutrisari Susu Dingin',
      //    'harga_jual' => 7000,
      //    'is_active' => true,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('stok_toko_keluar')->insert([
      //    'tenant_id' => 1,
      //    'kasir_id' => 5,
      //    'produk_toko_id' => 1,
      //    'jumlah_retur' => 1,
      //    'harga_produk' => 10000,
      //    'keterangan' => 'Retur/salah pesan',
      //    'created_at' => Carbon::now(),
      // ]);

      // shift seeder
      DB::table('shift')->insert([
         'tenant_id' => 1,
         'shift' => 'Pagi',
         'start_time' => '06:00:00',
         'end_time' => '13:59:59',
         'created_at' => Carbon::now(),
      ]);

      DB::table('shift')->insert([
         'tenant_id' => 1,
         'shift' => 'Siang',
         'start_time' => '14:00:00',
         'end_time' => '21:59:59',
         'created_at' => Carbon::now(),
      ]);

      DB::table('shift')->insert([
         'tenant_id' => 1,
         'shift' => 'Malam',
         'start_time' => '22:00:00',
         'end_time' => '05:59:59',
         'created_at' => Carbon::now(),
      ]);

      DB::table('shift')->insert([
         'tenant_id' => 2,
         'shift' => 'Pagi',
         'start_time' => '06:00:00',
         'end_time' => '13:59:59',
         'created_at' => Carbon::now(),
      ]);

      DB::table('shift')->insert([
         'tenant_id' => 2,
         'shift' => 'Siang',
         'start_time' => '14:00:00',
         'end_time' => '21:59:59',
         'created_at' => Carbon::now(),
      ]);

      DB::table('shift')->insert([
         'tenant_id' => 2,
         'shift' => 'Malam',
         'start_time' => '22:00:00',
         'end_time' => '05:59:59',
         'created_at' => Carbon::now(),
      ]);

      // DB::table('pengeluaran')->insert([
      //    'tenant_id' => 1,
      //    'deskripsi' => 'Pembelian bahan baku kopi',
      //    'jumlah' => 50000,
      //    'user_id' => 3,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('printer')->insert([
      //    'nama_printer' => 'epson',
      //    'lebar_kertas' => 88,
      //    'created_at' => Carbon::now(),
      // ]);

      // DB::table('catatan')->insert([
      //    'tenant_id' => 1,
      //    'catatan' => 'Perbaikan termos',
      //    'kasir_id' => 5,
      //    'admin_id' => 3,
      //    'created_at' => Carbon::now(),
      //    'updated_at' => Carbon::now(),
      // ]);

      // DB::table('pelanggans')->insert([
      //    'nama' => 'Suprianto',
      //    'alamat' => 'Jl segara',
      //    'telepon' => '086767234455',
      //    'poin' => 20,
      //    'created_at' => Carbon::now()
      // ]);
   }
}
