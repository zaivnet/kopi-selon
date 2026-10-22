@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Stok Keluar</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.stok-toko-keluar.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="row">

               <form method="GET" class="col-12 row g-2 mb-3">
                  <div class="col-md-3">
                     <input type="date" name="search_date" class="form-control"
                        value="{{ request('search_date', now()->format('Y-m-d')) }}">
                  </div>

                  <div class="col-md-6">
                     <button class="btn btn-outline-info rounded-pill">
                        Filter
                     </button>

                     @if(request()->filled('bulan'))
                     <a href="{{ route('admin.stok-keluar') }}" class="btn btn-outline-secondary rounded-pill">
                        Reset
                     </a>
                     @endif
                     <a href="{{route('admin.stok-keluar.add')}}">
                        <button type="button" class="btn btn-outline-primary rounded-pill">
                           Tambah
                        </button>
                     </a>
                  </div>
               </form>
            </div>
            <div class="table-responsive">
               <table class="table table-striped table-responsive" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Tanggal Keluar</th>
                        <th>Keterangan</th>
                        <th>User</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($stokKeluar as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->produkVarian->nama}}
                           <p class="badge bg-info">
                              {{
                              $item->jumlah_retur }}
                           </p>
                        </td>
                        <td>{{ 'Rp ' . number_format($item->harga_produk * $item->jumlah_retur, 0, ',', '.') ?? '-' }}
                        </td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y H:i') }}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-stok-keluar"
                                 data-id="{{ $item->id }}" data-produk="{{$item->produkVarian->nama}}"
                                 data-stok="{{$item->jumlah_retur}}" data-keterangan="{{$item->keterangan}}" {{
                                 auth()->user()->id == $item->user->id ? '' : 'disabled' }}>
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-stok-keluar"
                                 data-id="{{ $item->id }}" data-produk="{{$item->produkVarian->nama}}"
                                 data-stok="{{$item->jumlah_retur}}" {{ auth()->user()->id == $item->user->id ? '' :
                                 'disabled' }}>
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
   @include('pages.admin.stok-toko-keluar.edit')
</div>
@endsection