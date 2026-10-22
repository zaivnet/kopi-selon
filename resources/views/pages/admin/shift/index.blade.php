@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Manajemen Shift</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.shift.edit')
   @include('pages.admin.shift.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
                  data-bs-target="#modal-add-shift">
                  Tambah
               </button>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Shift</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Berakhir</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($shift as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->shift }}</td>
                        <td>{{ $item->start_time }}</td>
                        <td>{{ $item->end_time }}</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-shift"
                                 data-id="{{ $item->id }}" data-shift="{{ $item->shift }}"
                                 data-start_time="{{ $item->start_time }}" data-end_time="{{ $item->end_time }}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-shift"
                                 data-id="{{ $item->id }}" data-shift="{{$item->shift}}">
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
   @include('pages.admin.shift.add')
</div>
@endsection