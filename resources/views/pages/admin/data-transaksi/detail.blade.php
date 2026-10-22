@extends('layout.admin.app')
@section('content')
<section class="section mt-10">
   <h3>Detail Transaksi</h3>
   <div class="card">
      <div class="card-body">
         <h4 class="blink-retur">REQUEST RETUR</h4>
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
                        <form action="{{ route('admin.validasi', $row->id) }}" method="POST">
                           @csrf
                           @method('PUT')
                           <input type="hidden" name="id_validasi_retur" value="{{ $row->id }}">
                           @if($row->status_validasi == 'pending')
                           <button type="submit" name="status_validasi" value="diterima" class="btn btn-success btn-sm"
                              onclick="return confirm('Terima retur produk')">Approve</button>
                           <button type="submit" name="status_validasi" value="ditolak" class="btn btn-danger btn-sm"
                              onclick="return confirm('Tolak retur produk')">Reject</button>
                           @else
                           <button type="button" class="btn btn-secondary btn-sm" disabled>No Action</button>
                           @endif
                        </form>
                     </td>
                  </tr>
                  @endforeach
            </table>
         </div>
      </div>
   </div>
   <div class="card">
      <div class="card-body">
         <div class="row d-none d-lg-flex mb-3">
            <div class="col-md-4 flex-grow-1">
               <table>
                  <tr>
                     <th>Kode Transaksi</th>
                     <td class="px-2">:</td>
                     <td id="kodeTransaksiText">{{ $transaksi->kode_transaksi }}</td>
                  </tr>
                  <tr>
                     <th>Kasir</th>
                     <td class="px-2">:</td>
                     <td id="kasirText">{{ $kasir->name }}</td>
                  </tr>
                  <tr>
                     <th>Pelanggan</th>
                     <td class="px-2">:</td>
                     <td id="pelangganText">{{ $transaksi->nama_pelanggan}}</td>
                  </tr>
               </table>
            </div>
            <div class="col-md-3 flex-grow-0 flex-shrink-1">
               <table>
                  <tr>
                     <th>Total Item</th>
                     <td class="px-2">:</td>
                     <td>{{ $transaksi->total_item }}</td>
                  </tr>
                  <tr>
                     <th>Total Harga</th>
                     <td class="px-2">:</td>
                     <td id="totalHargaText">{{ 'Rp ' . number_format($transaksi->total_harga, 0, ',', '.') }}
                     </td>
                  </tr>
                  <tr>
                     <th>Bayar</th>
                     <td class="px-2">:</td>
                     <td id="bayarText">{{ 'Rp ' . number_format($transaksi->bayar, 0, ',', '.') }}</td>
                  </tr>
               </table>
            </div>
            <div class="col-md-5 flex-grow-1">
               <table>
                  <tr>
                     <th>Kembalian</th>
                     <td class="px-2">:</td>
                     <td id="kembalianText">{{ 'Rp ' . number_format($transaksi->kembalian, 0, ',', '.') }}</td>
                  </tr>
                  <tr>
                     <th>Metode Pembayaran</th>
                     <td class="px-2">:</td>
                     <td id="metodePembayaranText">
                        @if ($transaksi->metode_pembayaran == 'offline')
                        Tunai
                        @else
                        QRIS
                        @endif</td>
                  </tr>
                  <tr>
                     <th>Tanggal</th>
                     <td class="px-2">:</td>
                     <td id="tanggalTransaksiText">
                        {{ $tanggal_transaksi }}
                     </td>
                  </tr>
               </table>
            </div>
         </div>

         <div class="row d-flex d-md-block d-lg-none mb-3">
            <div class="col-md-4 flex-grow-1">
               <table>
                  <tr>
                     <th>Kode Transaksi</th>
                     <td class="px-2">:</td>
                     <td id="kodeTransaksiText">{{ $transaksi->kode_transaksi }}</td>
                  </tr>
                  <tr>
                     <th>Kasir</th>
                     <td class="px-2">:</td>
                     <td id="kasirText">{{ $kasir->name }}</td>
                  </tr>
                  <tr>
                     <th>Pelanggan</th>
                     <td class="px-2">:</td>
                     <td id="pelangganText">{{ $transaksi->nama_pelanggan}}</td>
                  </tr>
                  <tr>
                     <th>Total Item</th>
                     <td class="px-2">:</td>
                     <td>{{ $transaksi->total_item }}</td>
                  </tr>
                  <tr>
                     <th>Total Harga</th>
                     <td class="px-2">:</td>
                     <td id="totalHargaText">{{ 'Rp ' . number_format($transaksi->total_harga, 0, ',', '.') }}
                     </td>
                  </tr>
                  <tr>
                     <th>Bayar</th>
                     <td class="px-2">:</td>
                     <td id="bayarText">{{ 'Rp ' . number_format($transaksi->bayar, 0, ',', '.') }}</td>
                  </tr>
                  <tr>
                     <th>Kembalian</th>
                     <td class="px-2">:</td>
                     <td id="kembalianText">{{ 'Rp ' . number_format($transaksi->kembalian, 0, ',', '.') }}</td>
                  </tr>
                  <tr>
                     <th>Metode Pembayaran</th>
                     <td class="px-2">:</td>
                     <td id="metodePembayaranText">{{ $transaksi->metode_pembayaran }}</td>
                  </tr>
                  <tr>
                     <th>Tanggal</th>
                     <td class="px-2">:</td>
                     <td id="tanggalTransaksiText">
                        {{ $tanggal_transaksi }}
                     </td>
                  </tr>
               </table>
            </div>
         </div>

         <div class="table-responsive">
            <table class="table table-striped">
               <thead>
                  <tr>
                     <th>No</th>
                     <th>Nama Produk</th>
                     <th>Jumlah Item</th>
                     <th>Harga Satuan</th>
                     <th>Total Harga</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                  $total_harga = 0;
                  @endphp
                  @foreach($detail_transaksi as $row)
                  <tr>
                     <td>{{ $loop->iteration }}</td>
                     <td>{{ $row->nama_varian }}</td>
                     <td>{{ $row->jumlah }}</td>
                     <td>{{ 'Rp ' . number_format($row->harga_satuan, 0, ',', '.') }}</td>
                     <td>
                        {{ 'Rp ' . number_format($row->total_harga_produk, 0, ',', '.') }}
                        @php
                        $total_harga+=$row->total_harga_produk;
                        @endphp
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
            <div class="d-flex justify-content-end">
               <p style="font-size: 30px;"><strong>TOTAL :</strong> {{ 'Rp ' . number_format($total_harga, 0, ',', '.')
                  }}
               </p>
            </div>
         </div>
         <a href="{{ route('admin.data-transaksi') }}" class="btn btn-secondary btn-sm"> <i
               class="bi bi-arrow-left-square"></i>
            Kembali</a>
      </div>
   </div>
</section>
@endsection