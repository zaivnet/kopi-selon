@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Pengeluaran</h3>
         </div>
      </div>
   </div>
   @include('pages.admin.pengeluaran.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <form method="GET" id="filterPengeluaran" class="row g-2 mb-3">
                  <div class="col-12">
                     <input type="date" name="search_date" class="form-control"
                        value="{{ request('search_date', now()->format('Y-m-d')) }}"
                        onchange="document.getElementById('filterPengeluaran').submit();">
                  </div>
               </form>
               <div class="col-lg-6">
                  <h5>Total Pengeluaran :</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($total_pengeluaran, 0, ',', '.') }}
                  </h3>
               </div>
               <div class="col-lg-6">
                  <h5>Pengeluaran Kasir</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($pengeluaran_kasir, 0, ',', '.') }}
                  </h3>
                  <h5>Pengeluaran Admin</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($pengeluaran_admin, 0, ',', '.') }}
                  </h3>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <a href="{{ route('admin.pengeluaran.add') }}">
                  <button class="btn btn-outline-primary mb-3 rounded-pill">
                     Tambah
                  </button>
               </a>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($pengeluaran as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->deskripsi }}</td>
                        <td>{{ 'Rp ' . number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <a href="{{ route('admin.pengeluaran.edit', $item->id) }}">
                                 <button style="width: 40px; height:40px;"
                                    class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center">
                                    <i class=" bi bi-pencil p-0"></i>
                                 </button>
                              </a>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-pengeluaran"
                                 data-id="{{$item->id}}" data-deskripsi="{{$item->deskripsi}}"
                                 data-jumlah="{{$item->jumlah}}">
                                 <i class=" bi bi-trash p-0 pe-2"></i>
                              </button>
                           </div>
                        </td>
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