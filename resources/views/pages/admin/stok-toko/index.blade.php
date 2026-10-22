@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Stok Masuk</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.stok-toko.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
               <div class="col-md-3">
                  <input type="month" name="bulan" class="form-control"
                     value="{{ request('bulan', now()->format('Y-m')) }}">
               </div>

               <div class="col-md-1">
                  <button class="btn btn-outline-info rounded-pill">
                     Filter
                  </button>
               </div>

               @if(request()->filled('bulan'))
               <div class="col-md-1">
                  <a href="{{ route('admin.stok', $tenant->id) }}" class="btn btn-outline-secondary rounded-pill">
                     Reset
                  </a>
               </div>
               @endif
            </form>
            <div class="table-responsive">

               <table class="table table-striped table-responsive" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Stok Masuk</th>
                        <th>Tanggal Masuk</th>
                        <th>Admin</th>
                        <th>Aksi</th>
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
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-stok-toko"
                                 data-id="{{ $item->id }}" data-produk-id="{{ $item->produk_id }}"
                                 data-produk="{{$item->produk->nama_produk}}" data-stok="{{$item->stok_masuk}}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-stok-toko"
                                 data-id="{{ $item->id }}" data-produk="{{$item->produk->nama_produk}}"
                                 data-stok="{{$item->stok_masuk}}" data-tanggal="{{$item->created_at}}">
                                 <i class="bi bi-trash p-0 pe-2"></i>
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
   {{-- @include('pages.admin.kategori.add') --}}
   @include('pages.admin.stok-toko.edit')
</div>
@endsection