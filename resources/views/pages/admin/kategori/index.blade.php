@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Kategori</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.kategori.edit')
   @include('pages.admin.kategori.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
                  data-bs-target="#modal-add-kategori">
                  Tambah
               </button>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($kategori as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-kategori"
                                 data-id="{{ $item->id }}" data-kategori="{{ $item->nama_kategori }}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-kategori"
                                 data-id="{{ $item->id }}" data-kategori="{{$item->nama_kategori}}">
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
   @include('pages.admin.kategori.add')
</div>
@endsection