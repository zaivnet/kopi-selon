@extends('layout.kasir.app')
@section('content')
<?php

use Illuminate\Support\Carbon;
?>
<section class="row">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-between">
               <div class="fw-bolder">Shift {{$shift->shift}}</div>
               <div class="fw-bolder">{{$shift->start_time}} - {{$shift->end_time}}</div>
            </div>
         </div>
      </div>
   </div>
</section>
<div class="card">
   <div class="card-body">
      <div class="mb-2 d-flex justify-content-between flex-wrap gap-3">
         <div class="col-12 col-md-4">
            <form action="" method="GET" id="filterForm">
               <select name="metode_pembayaran" class="form-control"
                  onchange="document.getElementById('filterForm').submit()">
                  <option value="all">Semua Metode Pembayaran</option>
                  <option value="offline" {{ request('metode_pembayaran')=='offline' ? 'selected' : '' }}>Tunai</option>
                  <option value="online" {{ request('metode_pembayaran')=='online' ? 'selected' : '' }}>QRIS</option>
               </select>
            </form>
         </div>
         <a href="{{ route('kasir.historiTransaksi.export', request()->query()) }}"
            class="btn btn-outline-success rounded-pill col-md-2">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
         </a>
      </div>
      <div class="table-responsive">
         <table class="table table-striped" id="table1">
            <thead>
               <tr>
                  <th>No</th>
                  <th>Kode Transaksi</th>
                  <th>Kasir</th>
                  <th>Pelanggan</th>
                  <th>Total Item</th>
                  <th>Total Harga</th>
                  <th>Bayar</th>
                  <th>Kembalian</th>
                  <th>Pembayaran</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
               </tr>
            </thead>
            <tbody>
               @php
               $total_transaksi = 0;
               @endphp
               @foreach($transaksi as $row)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->kode_transaksi }}</td>
                  <td>{{ $row->nama_kasir }}</td>
                  <td>{{ $row->nama_pelanggan }}</td>
                  <td>{{ $row->total_item}}</td>
                  <td>{{ 'Rp ' . number_format($row->total_harga, 0, ',', '.') }}</td>
                  @php
                  $total_transaksi += $row->total_harga;
                  @endphp
                  <td>{{ 'Rp ' . number_format($row->bayar, 0, ',', '.') }}</td>
                  <td>{{ 'Rp ' . number_format($row->kembalian, 0, ',', '.') }}</td>
                  <td>
                     @if ($row->metode_pembayaran == 'offline')
                     Tunai
                     @else
                     QRIS
                     @endif
                  </td>
                  <td>
                     {{ $row->status }}
                  </td>
                  <td>
                     <?php
                            echo Carbon::parse($row->created_at)->locale('id')->translatedFormat('d F Y H:i');
                            ?>
                  </td>
                  <td>
                     <a href="{{ route('kasir.detailTransaksi', $row->id_transaksi) }}"
                        class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center"
                        style="width: 40px; height:40px;"><i class="bi bi-eye-fill"></i></a>
                  </td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
      <div class="row d-flex justify-content-between mt-4">
         <div class="col-md-6">
            <h4>Item Terjual : {{$rekap->total_item}}</h4>
            <h4>Hasil Penjualan : {{ 'Rp' .
               number_format($rekap->total_penjualan, 0, ',', '.')
               }}
            </h4>
         </div>
         <div class="col-md-6 text-md-end">
            <h4>Pembayaran QRIS : Rp{{number_format($rekap->pembayaran_online, 0, ',', '.')}}</h4>
            <h4>Pembayaran Tunai : Rp{{number_format($rekap->pembayaran_offline, 0, ',', '.')}}</h4>
         </div>
      </div>
   </div>
</div>

<h4>Pembatalan & Retur</h4>
<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-grid">
            <thead>
               <tr>
                  <th>No</th>
                  <th>No Transaksi</th>
                  <th>Nama Produk</th>
                  <th>Total Harga</th>
                  <th>Jenis Request</th>
                  <th>Status</th>
                  <th>Keterangan</th>
                  <th>Aksi</th>
               </tr>
            </thead>
            <tbody>
               @foreach($dataRetur as $row)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->kode }}</td>
                  <td>{{ $row->nama_varian }} <span class="badge bg-danger">{{ $row->jumlah_item }}</span></td>
                  <td>{{ 'Rp ' . number_format($row->harga_item, 0, ',', '.') }}</td>
                  <td>{{Str::title($row->type)}}</td>
                  <td>
                     @if($row->status_validasi == 'pending')
                     <span class="badge bg-secondary">Pending</span>
                     @elseif($row->status_validasi == 'diterima')
                     <span class="badge bg-success">Diterima</span>
                     @elseif($row->status_validasi == 'ditolak')
                     <span class="badge bg-danger">Ditolak</span>
                     @endif
                  </td>
                  <td>{{ $row->keterangan }}</td>
                  <td>
                     <a href="{{ route('kasir.detailTransaksi', $row->transaksi_id) }}"
                        class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center"
                        style="width: 40px; height:40px;"><i class="bi bi-eye-fill"></i></a>
                  </td>
               </tr>
               @endforeach
         </table>
      </div>
   </div>
</div>

<h3 class="mt-0">Produk Terlaris</h3>
<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-striped">
            <thead>
               <tr>
                  <th>No</th>
                  <th>Nama Produk</th>
                  <th>Varian</th>
                  <th>Total Terjual</th>
                  <th>Harga Penjualan</th>
               </tr>
            </thead>
            <tbody>
               @foreach($produkTerlaris as $item)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->nama_produk }}</td>
                  <td>{{$item->nama_varian}}</td>
                  <td>{{ $item->total_terjual }}</td>
                  <td>{{ 'Rp ' . number_format($item->total_penjualan, 0, ',', '.') }}</td>
               </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
</div>
@endsection