@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Manajemen Kasir</h3>
         </div>
      </div>
   </div>
   @include('pages.admin.kasir.edit')
   @include('pages.admin.kasir.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
                  data-bs-target="#modal-add-kasir">
                  Tambah
               </button>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($kasir as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->email }}</td>
                        <td><span class="badge {{ $item->isActive ? 'bg-success' : 'bg-danger' }}">{{ $item->isActive ?
                              'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-kasir"
                                 data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                 data-username="{{ $item->username }}" data-email="{{ $item->email }}"
                                 data-isactive=" {{ $item->isActive }}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;" class="btn 
                                 {{$item->isActive == 1 ? 'btn-outline-danger' : 'btn-outline-primary'}}
                                btn-md rounded-pill d-flex justify-content-center btn-hapus-kasir"
                                 data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                                 @if ($item->isActive == 1)
                                 <i class="bi bi-trash p-0 pe-2"></i>
                                 @else
                                 <i class="bi bi-arrow-clockwise p-0 pe-2"></i>
                                 @endif
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
   @include('pages.admin.kasir.add')
</div>
@endsection