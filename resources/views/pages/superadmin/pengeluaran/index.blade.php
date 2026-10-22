@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>{{ $title }}</h3>
         </div>
      </div>
   </div>
   <section class="section">

      <div class="card">
         <div class="card-body">
            <form method="GET" action="{{ route('superadmin.pengeluaran') }}" class="mb-4">
               <div class="row g-1">

                  {{-- Select toko --}}
                  <div class="col-md-4">
                     <label>Toko:</label>
                     <select name="toko_id" class="form-control">
                        <option value="">Semua Toko</option>
                        @foreach ($toko as $t)
                        <option value="{{ $t->id }}" {{ request('toko_id')==$t->id ? 'selected' : '' }}>
                           {{ $t->name }}
                        </option>
                        @endforeach
                     </select>
                  </div>

                  {{-- Input tanggal --}}
                  <div class="col-md-4">
                     <label>Tanggal:</label>
                     <input type="date" name="tanggal" value="{{ $tanggalAktif ?? '' }}" class="form-control">
                  </div>

                  <div class="col-md-4 d-flex align-items-end gap-2">
                     {{-- Tombol filter --}}
                     <div class="mt-4">
                        <button class="btn btn-primary">Filter</button>
                     </div>

                     {{-- Tombol reset --}}
                     <div class="mt-4">
                        <a href="{{ route('superadmin.pengeluaran') }}" class="btn btn-secondary">Reset</a>
                     </div>
                  </div>

               </div>
            </form>
            <div class="table-responsive">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Toko</th>
                        <th>Deskripsi</th>
                        <th>Tanggal</th>
                        <th>Admin</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if ($pengeluaran->isEmpty())
                     <tr>
                        <td colspan="6" class="text-center">Tidak ada data pengeluaran.</td>
                     </tr>
                     @else

                     @foreach ($pengeluaran as $index => $item)
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->tenant->name }}</td>
                        <td>{{ $item->deskripsi }}
                           <br>
                           <small class="badge bg-info">{{ 'Rp ' . number_format($item->jumlah, 0, ',', '.')
                              }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i') }}</td>
                        <td>{{ $item->user->name }}</td>

                     </tr>
                     @endforeach
                     @endif
                  </tbody>
               </table>
            </div>
            <div class="d-flex justify-content-center">
               {{ $pengeluaran->links('pagination::bootstrap-5') }}
            </div>
         </div>
      </div>
   </section>
</div>
@endsection