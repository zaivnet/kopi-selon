@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Catatan</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">

            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Catatan</th>
                        <th>Kasir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($catatan as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y h:m') }}</td>
                        <td>{{ $item->catatan }}</td>
                        <td>{{ $item->kasir->name }}</td>
                        <td>
                           @if ($item->admin_id == null)
                           <strong class="badge bg-secondary">Pending</strong>
                           @else
                           <span class="badge bg-success">Ditangani oleh <strong>
                                 {{ $item->admin->name }} </strong></span>
                           @endif
                        </td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <a href="{{route('admin.catatan.toggle', $item->id)}}">
                                 @if ($item->admin_id == null)
                                 <button type="button" style="width: 40px; height:40px;"
                                    class="btn btn-outline-success btn-md rounded-pill d-flex justify-content-center btn-edit-catatan">
                                    <i class=" bi bi-check-lg p-0"></i>
                                 </button>
                                 @else
                                 <button type="button" style="width: 40px; height:40px;"
                                    class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-catatan">
                                    <i class=" bi bi-arrow-clockwise p-0"></i>
                                 </button>
                                 @endif
                              </a>
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
   @include('pages.kasir.catatan.add')
</div>
@endsection