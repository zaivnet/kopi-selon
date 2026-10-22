@extends('layout.superadmin.app')
@section('content')
<div class="content-wrapper container">
   <div class="page-heading">
      <div class="row mt-10">
         <div class="col-12 d-flex justify-content-between align-items-center mt-2">
            <h3 class="text-center">Daftar Toko</h3>
            <a href="{{route('superadmin.toko.add')}}">
               <button style="width: 40px; height:40px;"
                  class="btn btn-outline-primary btn-md rounded-pill d-flex justify-content-center">
                  <i class="bi bi-plus p-0"></i>
               </button>
            </a>
         </div>
      </div>
   </div>
   <div class="page-content">
      <section class="row">
         @if($data->isEmpty())
         <div class="col-12">
            <div class="card">
               <div class="card-body">
                  <div class="text-center">
                     <h5 class="card-title text-danger">Belum ada toko yang tersedia.</h5>
                  </div>
               </div>
            </div>
         </div>
         @else
         @foreach($data as $t)
         <div class="col-12">
            <a href="{{route('superadmin.toko.edit', $t->id)}}" style="text-decoration: none;">
               <div class=" card">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h5 class="card-title">{{ $t->name }}
                              @if ($t->isActive)
                              <span class="badge bg-success">Aktif</span>
                              @else
                              <span class="badge bg-danger">Tidak Aktif</span>
                              @endif
                           </h5>
                           <p class="card-text">{{ $t->address }}</p>
                        </div>
                     </div>

                  </div>
               </div>
            </a>
         </div>
         @endforeach
         @endif
      </section>
   </div>

</div>
@endsection