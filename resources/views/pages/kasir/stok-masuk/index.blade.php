@extends('layout.kasir.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Stok Masuk | {{\Carbon\Carbon::now()->translatedFormat('l, d F Y')}}</h3>
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
                        <th>Stok Masuk</th>
                        <th>Tanggal Masuk</th>
                        <th>Admin</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($stok as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->produk->nama_produk }}</td>
                        <td>{{ $item->stok_masuk }}</td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y') }}</td>
                        <td>{{ $item->user->name }}</td>

                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
@endsection