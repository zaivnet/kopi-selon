@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Pendapatan</h3>
         </div>
      </div>
   </div>
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="row">
               {{-- =========================
               FILTER RENTANG TANGGAL
               ========================= --}}
               <form method="GET" action="{{ route('superadmin.pendapatan') }}" class="mb-3">
                  <div class="row g-2 align-items-end">

                     {{-- TANGGAL MULAI --}}
                     <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                     </div>

                     {{-- TANGGAL AKHIR --}}
                     <div class="col-md-4">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                     </div>

                     <div class="col-md-4">
                        <label class="form-label">Toko</label>
                        <select name="tenant_id" class="form-select">
                           @foreach ($tenants as $t)
                           <option value="{{ $t->id }}" {{ $tenant->id == $t->id ? 'selected' : '' }}>
                              {{ $t->name }}
                           </option>
                           @endforeach
                        </select>
                     </div>

                     {{-- TOMBOL --}}
                     <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary rounded-pill">
                           <i class="bi bi-filter"></i> Filter
                        </button>
                     </div>

                  </div>
               </form>

               <div class="border-bottom mb-3">
                  <h5>Produk Terjual : {{ $totalBulanan['jumlah_produk_terjual'] }} item</h5>
                  <h5>Hasil Penjualan : Rp{{
                     number_format($totalBulanan['total_penjualan'], 0, ',', '.')}}</h5>
                  <h6 class="fw-light">Pembayaran QRIS : Rp{{ number_format($totalBulanan['penjualan_online'], 0, ',',
                     '.') }}</h6>
                  <h6 class="fw-light">Pembayaran Tunai : Rp{{ number_format($totalBulanan['penjualan_offline'], 0, ',',
                     '.') }}</h6>
                  <h6 class="fw-light">pengeluaran Toko : Rp{{ number_format($totalBulanan['pengeluaran_toko'], 0, ',',
                     '.') }}</h6>
                  <h6 class="fw-bold">Laba Tunai : Rp{{ number_format($totalBulanan['penjualan_offline'] -
                     $totalBulanan['pengeluaran_toko'], 0, ',',
                     '.') }}</h6>
               </div>
               <div class="border-bottom mb-3">
                  <h5>Produk Keluar : {{ $totalBulanan['total_jumlah_retur'] }} item</h5>
                  <h6 class="fw-light">Harga Produk Keluar : Rp{{ number_format($totalBulanan['total_produk_keluar'], 0,
                     ',', '.') }}
                  </h6>

               </div>
               <h5>Laba Bersih : Rp{{ number_format($totalBulanan['laba_bersih'], 0, ',', '.') }}</h5>
            </div>
         </div>
      </div>
   </section>
   <section class="section">
      <div class="card">
         <div class="card-header">

         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Produk Terjual</th>
                        <th>QRIS</th>
                        <th>Tunai</th>
                        <th>Produk Keluar</th>
                        <th>Pengeluaran</th>
                        <th>Laba Bersih</th>
                        <th>Tanggal</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if ($laporan->isEmpty())
                     <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                     </tr>
                     @else
                     @foreach ($laporan as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="badge bg-success">{{ $item->jumlah_produk_terjual }}</span> {{ 'Rp '
                           . number_format($item->total_harga_penjualan_produk, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($item->penjualan_online, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($item->penjualan_offline, 0, ',', '.') }}</td>

                        <td><span class="badge bg-danger">{{ $item->total_jumlah_retur }}</span> {{ 'Rp ' .
                           number_format($item->total_produk_keluar, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                        <td>{{ 'Rp ' . number_format($item->laba_bersih, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>

                     </tr>
                     @endforeach
                     @endif

                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
@endsection