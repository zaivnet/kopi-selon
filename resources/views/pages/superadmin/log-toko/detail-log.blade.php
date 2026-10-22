@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3> Log {{$produkVarian->nama_varian}}</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="table-responsive">

               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th>Jam</th>
                        <th>Stok Masuk</th>
                        <th>Stok Keluar</th>
                        <th>Terjual</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse ($logs as $log)
                     <tr>
                        <td>{{ $log->jam }}</td>
                        <td>{{ $log->stok_masuk }}</td>
                        <td>{{ $log->stok_keluar }}</td>
                        <td>{{ $log->terjual }}</td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="4" class="text-center">Tidak ada aktivitas</td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
            <div class="d-flex justify-content-between">
               {{-- <h5>Total : {{$total->total_jumlah_keluar}} Item</h5> --}}
            </div>
         </div>
      </div>
   </section>

</div>
@endsection