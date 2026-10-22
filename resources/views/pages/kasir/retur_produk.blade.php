@extends('layout.kasir.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Pembatalan & Retur Transaksi</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-responsive" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Tanggal Keluar</th>
                        <th>Request</th>
                        <th>Keterangan</th>
                        <th>Kasir</th>
                        <th>Admin</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($batal_retur as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->detailTransaksi->produkVarian->nama}}
                           <p class="badge bg-danger">
                              {{
                              $item->jumlah_item }}
                           </p>
                        </td>
                        <td>{{ 'Rp ' . number_format($item->harga_item, 0, ',', '.') ?? '-' }}
                        </td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y H:i') }}</td>
                        <td>{{Str::title($item->type)}}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td>{{ $item->transaksi->kasir->name }}</td>
                        <td>{{ $item->admin->name }}</td>

                     </tr>
                     @endforeach
                  </tbody>
               </table>
               <div class="d-flex justify-content-end mt-2">
                  <h4>Total: {{ 'Rp ' . number_format($totalHarga, 0, ',', '.') }}</h4>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>
@endsection