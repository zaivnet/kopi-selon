@extends('layout.admin.app')
@section('content')
<section class="section mt-10">
   <div class="section-header">
      <h3>Data Transaksi</h3>
   </div>
   <div class="card">
      <div class="card-body">
         <form method="GET" class="row g-2">
            <!-- FILTER TANGGAL -->
            <div class="col-md-4">
               <label>Dari Tanggal</label>
               <input type="datetime-local" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>

            <div class="col-md-4">
               <label>Sampai Tanggal</label>
               <input type="datetime-local" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
               <label>Pembayaran</label>
               <select name="metode_pembayaran" class="form-control">
                  <option value="all">Semua Metode Pembayaran</option>
                  <option value="offline" {{ request('metode_pembayaran')=='offline' ? 'selected' : '' }}>Tunai</option>
                  <option value="online" {{ request('metode_pembayaran')=='online' ? 'selected' : '' }}>QRIS</option>
               </select>
            </div>
            <div class="col-md-12 d-flex align-items-end">
               <button class="btn btn-outline-primary rounded-pill me-2">
                  <i class="bi bi-filter"></i> Filter
               </button>
               <a href="{{ route('admin.dataTransaksi.export', [
                  'start_date' => request('start_date'),
                  'end_date' => request('end_date')
               ]) }}" class="btn btn-outline-success rounded-pill">
                  <i class="bi bi-file-earmark-excel"></i> Export Excel
               </a>
            </div>
         </form>
      </div>
   </div>
   @if($validasi_retur->count() > 0)
   <div class="card">
      <div class="card-body">
         <h4 class="blink-retur">Request Pembatalan & Retur</h4>
         <div class="table-responsive">
            <table class="table table-grid">
               <thead>
                  <tr>
                     <th>No</th>
                     <th>Nama Produk</th>
                     <th>Total Harga</th>
                     <th>Jenis Request</th>
                     <th>Status Validasi</th>
                     <th>Keterangan</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($validasi_retur as $row)
                  <tr>
                     <td>{{ $loop->iteration }}</td>
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
                        <a href="{{ route('admin.detailTransaksi', $row->transaksi_id) }}"
                           class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center"
                           style="width: 40px; height:40px;"><i class="bi bi-eye-fill"></i></a>
                     </td>
                  </tr>
                  @endforeach
            </table>
         </div>
      </div>
   </div>
   @endif
   <div class="card">
      <div class="card-body">
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
                        <?php
                        echo \Carbon\Carbon::parse($row->created_at)->locale('id')->translatedFormat('d M Y H:i');
                        ?>
                     </td>
                     <td>
                        <a href="{{ route('admin.detailTransaksi', $row->id_transaksi) }}"
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
               <h4>Item Terjual : {{$totalTransaksi->total_item_terjual}}</h4>
               <h4>Hasil Penjualan : {{ 'Rp' .
                  number_format($totalTransaksi->total_hasil_penjualan, 0, ',', '.')
                  }}
               </h4>
            </div>
            <div class="col-md-6 text-md-end">
               <h4>Pembayaran QRIS : Rp{{number_format($totalTransaksi->total_penjualan_online, 0, ',', '.')}}</h4>
               <h4>Pembayaran Tunai : Rp{{number_format($totalTransaksi->total_penjualan_offline, 0, ',', '.')}}</h4>
            </div>
         </div>
      </div>
   </div>

   <h4>Pembatalan & Retur</h4>
   <div class="card">
      <div class="card-body">
         <div class="table-responsive">
            <table class="table table-grid" id="table2">
               <thead>
                  <tr>
                     <th>No</th>
                     <th>No Transaksi</th>
                     <th>Nama Produk</th>
                     <th>Total Harga</th>
                     <th>Jenis Request</th>
                     <th>Status Validasi</th>
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
                        <a href="{{ route('admin.detailTransaksi', $row->transaksi_id) }}"
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
            <table class="table table-striped" id="table2">
               <thead>
                  <tr>
                     <th>No</th>
                     <th>Nama Produk</th>
                     <th>Nama Varian</th>
                     <th>Total Terjual</th>
                     <th>Harga Penjualan</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($produkTerlaris as $item)
                  <tr>
                     <td>{{ $loop->iteration }}</td>
                     <td>{{ $item->nama_produk }}</td>
                     <td>{{ $item->nama_varian }}</td>
                     <td>{{ $item->total_terjual }}</td>
                     <td>{{ 'Rp ' . number_format($item->total_penjualan, 0, ',', '.') }}</td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>
</section>
@endsection