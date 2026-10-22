@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>{{$title}}</h3>
         </div>
      </div>
   </div>


   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
                  data-bs-target="#modal-add-gudang">
                  Tambah
               </button>
            </div>
            <div class="table-responsive">
               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Akses Toko</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($data as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                           @if ($item->tenants->isEmpty())
                           <span class="badge bg-secondary">Belum ada akses</span>
                           @else
                           @foreach ($item->tenants as $tenant)
                           <span class="badge bg-info mb-1">
                              {{ $tenant->name }}
                           </span>
                           @endforeach
                           @endif
                        </td>
                        {{-- <td><span class="badge {{ $item->isActive ? 'bg-success' : 'bg-danger' }}">{{
                              $item->isActive ?
                              'Aktif' : 'Nonaktif' }}</span></td> --}}
                        {{-- <td>
                           @if($item->tenants->count() > 0)
                           @foreach($item->tenants as $tenant)
                           <span class="badge bg-primary">{{ $tenant->name }}</span>
                           @endforeach
                           @else
                           <span class="text-muted">Tidak ada akses</span>
                           @endif
                        </td> --}}
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <a href="{{route('superadmin.gudang.edit', $item->id)}}">
                                 <button style="width: 40px; height:40px;"
                                    class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center">
                                    <i class="bi bi-pencil p-0"></i>
                                 </button>
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
   @include('pages.superadmin.gudang.add')
</div>
@endsection