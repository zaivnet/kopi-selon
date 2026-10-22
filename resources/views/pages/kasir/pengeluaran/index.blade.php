@extends('layout.kasir.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Pengeluaran</h3>
         </div>
      </div>
   </div>
   <section class="row">
      <div class="col-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex justify-content-between">
                  <div class="fw-bolder">Shift {{$shiftAktif->shift}}</div>
                  <div class="fw-bolder">{{$shiftAktif->start_time}} - {{$shiftAktif->end_time}}</div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-lg-6">
                  <h5>Total Pengeluaran :</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($total_pengeluaran, 0, ',', '.') }}
                  </h3>
               </div>
               <div class="col-lg-6">
                  <h5>Pengeluaran Kasir</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($pengeluaran_kasir, 0, ',', '.') }}
                  </h3>
                  <h5>Pengeluaran Admin</h5>
                  <h3 class="text-danger fw-bold">
                     {{ 'Rp ' . number_format($pengeluaran_admin, 0, ',', '.') }}
                  </h3>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section class="section">
      <div class="card">
         <div class="card-body">
            <div class="d-flex justify-content-end">
               <a href="{{ route('kasir.pengeluaranToko.add') }}">
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
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($pengeluaran as $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->deskripsi }}</td>
                        <td>{{ 'Rp ' . number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <a href="{{ route('kasir.pengeluaranToko.edit', $item->id) }}" @if ($item->user_id !=
                                 auth()->user()->id)
                                 onclick="return false;" style="pointer-events: none; cursor: default;"
                                 @endif>
                                 <button style="width: 40px; height:40px;"
                                    class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center">
                                    <i class=" bi bi-pencil p-0"></i>
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
</div>
@endsection