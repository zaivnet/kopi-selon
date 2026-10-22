@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Printer Setting</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.printer.edit')
   @include('pages.admin.printer.delete')
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
                  data-bs-target="#modal-add-printer">
                  Tambah
               </button>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Printer</th>
                        <th>Lebar Kertas</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($data as $item)

                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_printer }}</td>
                        <td>{{ $item->lebar_kertas }}mm</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-printer"
                                 data-id="{{ $item->id }}" data-nama="{{ $item->nama_printer }}"
                                 data-kertas="{{ $item->lebar_kertas }}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-printer"
                                 data-id="{{ $item->id }}" data-printer="{{$item->nama_printer}}">
                                 <i class="bi bi-trash p-0 pe-2" {{-- jika hanya ada 1 data maka tombol hapus disable
                                    --}} @if (count($data)=='1' ) disabled @endif></i>
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
   @include('pages.admin.printer.add')
</div>
@endsection