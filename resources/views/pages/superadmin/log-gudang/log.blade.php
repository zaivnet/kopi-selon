@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3> Log Gudang</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <div>
               <form method="GET" class="row g-2 mb-3">
                  <div class="col-md-4">
                     <select name="gudang_id" class="form-select">
                        @foreach ($gudang as $g)
                        <option value="{{ $g->id }}" {{ $g->id == $gudangAktifId ? 'selected' : '' }}>
                           {{ $g->name }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-4">
                     <input type="date" name="tanggal" class="form-control" value="{{$tanggalAktif}}">
                  </div>
                  <div class="col-md-2">
                     <button class="btn btn-outline-info rounded-pill">
                        Filter
                     </button>
                  </div>

               </form>
            </div>

            <div class="table-responsive">

               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>Produk</th>
                        <th class="text-end">Stok Masuk</th>
                        <th class="text-end">Stok Keluar</th>
                        <th class="text-end">Stok Sekarang</th>
                        <th>Detail</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($log as $item)
                     <tr>
                        <td>{{ $item->nama_produk }}</td>
                        <td class="text-end">{{ $item->total_masuk }}</td>
                        <td class="text-end">{{ $item->total_keluar }}</td>
                        <td class="text-end fw-bold">
                           {{ $item->stok_gudang }}
                        </td>
                        <td>
                           <a href="{{ route('superadmin.log.gudang.detail-log', [
                                 'gudang_id' => $item->gudang_id,
                                 'produkId' => $item->id,
                                 'tanggal' => $tanggalAktif
                           ]) }}">
                              <button style="width: 40px; height:40px; padding-top: 8px"
                                 class="btn btn-sm btn-outline-info rounded-pill d-flex justify-content-center">
                                 <i class="bi bi-eye-fill p-0"></i>
                              </button>
                           </a>
                        </td>
                     </tr>

                     @endforeach
                  </tbody>
               </table>
            </div>
            <div class="d-flex justify-content-between">
               {{-- <h5>Total : {{$total->total_jumlah_keluar}} Item</h5> --}}
            </div>
         </div>
      </div>
   </section>

</div>
@endsection