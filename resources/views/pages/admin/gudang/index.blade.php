@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>@foreach ($gudang as $item)
               {{ $item->name }}
               @endforeach</h3>
         </div>
      </div>
   </div>

   {{-- @include('pages.admin.gudang.edit') --}}
   @include('pages.admin.gudang.delete')

   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <a href="{{route('admin.produk.add')}}">

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
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Stok Gudang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($produk as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->kode }}
                        <td>{{ $item->nama_produk }}
                           <p>
                              <small class="badge bg-info">{{ $item->kategori->nama_kategori }}</small>
                           </p>
                        </td>

                        <td><span
                              class="text-sm badge {{ $item->stok_gudang == 0 ? 'bg-danger' : ($item->stok_gudang <= 50 ? 'bg-warning text-dark' : 'bg-success') }}">{{
                              $item->stok_gudang }}</span></td>
                        <td>
                           @if ($item->is_displayed)
                           <span class="badge bg-success">Tampil</span>
                           @elseif ($item->has_pivot)
                           <span class="badge bg-secondary">Nonaktif</span>
                           @else
                           <span class="badge bg-light text-dark">Belum Ditampilkan</span>
                           @endif
                        </td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <a href="{{route('admin.produk.edit', $item->id)}}">
                                 <button style="width: 40px; height:40px;"
                                    class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-produk"
                                    data-id="{{ $item->id }}" data-produk="{{ $item->nama_produk}}"
                                    data-kategori="{{$item->kategori->id}}" data-produk-id="{{$item->id}}"
                                    data-tenant-id="{{$tenant->id}}">
                                    <i class="bi bi-pencil p-0"></i>
                                 </button>
                              </a>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-info btn-md rounded-pill d-flex justify-content-center btn-tambah-stok-gudang"
                                 data-bs-toggle="modal" data-bs-target="#modal-add-stok-gudang"
                                 data-id="{{ $item->id }}" data-produk="{{ $item->nama_produk }}">
                                 <i class=" bi bi-plus p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-produk"
                                 data-id="{{ $item->id }}" data-produk="{{ $item->nama_produk }}">
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
   {{-- @include('pages.admin.gudang.add') --}}

</div>
@include('pages.admin.stok-gudang-masuk.add')
@endsection