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
            <div>
               <form method="GET" action="{{ route('superadmin.gudang.barang-keluar') }}" class="row g-2 mb-3">

                  {{-- FILTER GUDANG --}}
                  <div class="col-md-4">
                     <label>Gudang</label>
                     <select name="gudang" class="form-select">
                        <option value="">-- Semua Gudang --</option>
                        @foreach ($daftarGudang as $g)
                        <option value="{{ $g->id }}" {{ request('gudang')==$g->id ? 'selected' : '' }}>
                           {{ $g->name }}
                        </option>
                        @endforeach
                     </select>
                  </div>

                  {{-- FILTER TANGGAL --}}
                  <div class="col-md-3">
                     <label>Tanggal</label>
                     <input type="date" name="tanggal" class="form-control" value="{{ $tanggalAktif }}">
                  </div>

                  {{-- BUTTON --}}
                  <div class="col-md-3 d-flex align-items-end gap-2">
                     <button class="btn btn-outline-primary rounded-pill">Filter
                     </button>

                     <a href="{{ route('superadmin.gudang.barang-keluar') }}"
                        class="btn btn-outline-secondary rounded-pill">
                        Reset
                     </a>
                  </div>

               </form>

            </div>

            <div class="table-responsive">

               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Stok Keluar</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>Admin</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if ($riwayat->isEmpty())
                     <tr>
                        <td colspan="7" class="text-center">Tidak ada data.</td>
                     </tr>
                     @else
                     @foreach ($riwayat as $index => $item)
                     <tr>
                        <td>{{ $riwayat->firstItem() + $loop->index }}</td>
                        <td>{{ $item->produk->nama_produk }}</td>
                        <td>{{ $item->qty_keluar }}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y') }}</td>
                        <td>{{ $item->admin->name }}</td>
                     </tr>
                     @endforeach
                     @endif
                  </tbody>
               </table>
            </div>
            <div class="d-flex justify-content-center">
               {{ $riwayat->links('pagination::bootstrap-5') }}
            </div>
            <div class="d-flex justify-content-between">
               <h5>Total : {{$total->total_qty_keluar}} Item</h5>
            </div>
         </div>
      </div>
   </section>
   {{-- @include('pages.admin.produk.add') --}}

</div>
{{-- @include('pages.admin.stok.add') --}}
@endsection