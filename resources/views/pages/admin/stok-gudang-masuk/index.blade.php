@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3> Stok Masuk @foreach ($gudang as $item)
               {{ $item->name }}
               @endforeach</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.stok-gudang-masuk.edit')
   @include('pages.admin.stok-gudang-masuk.delete')

   <section class="section">
      <div class="card">
         <div class="card-body">
            <div>
               <form method="GET" action="{{ route('admin.gudang.stok-masuk', $tenant->id) }}" class="row g-2 mb-3">

                  <div class="col-md-4">
                     <label>Bulan</label>
                     <input type="month" name="bulan" class="form-control"
                        value="{{ request('bulan', $bulanAktif->format('Y-m')) }}">
                  </div>

                  <div class="col-md-4">
                     <label>Nama Produk</label>
                     <input type="text" name="produk" class="form-control" placeholder="Cari nama produk..."
                        value="{{ request('produk') }}">
                  </div>

                  <div class="col-md-4 d-flex align-items-end">
                     <button class="btn btn-outline-info me-2 rounded-pill" type="submit">
                        Filter
                     </button>

                     <a href="{{ route('admin.gudang.stok-masuk', $tenant->id) }}"
                        class="btn btn-outline-secondary me-2 rounded-pill">
                        Reset
                     </a>
                     <a href="{{ route('admin.gudang.stok-masuk.export', $tenant->id) }}?bulan={{ request('bulan') }}&produk={{ request('produk') }}"
                        class="btn btn-outline-success rounded-pill"><i class="bi bi-file-earmark-excel"> </i>
                     </a>
                  </div>
               </form>

            </div>

            <div class="table-responsive">

               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Stok Masuk</th>
                        <th>Harga Pembelian</th>
                        <th>Tanggal Masuk</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @if ($riwayat->isEmpty())
                     <tr>
                        <td colspan="8" class="text-center">Tidak ada data.</td>
                     </tr>
                     @else
                     @foreach ($riwayat as $index => $item)
                     <tr>
                        <td>{{ $riwayat->firstItem() + $loop->index }}</td>
                        <td>{{ $item->produk->kode }}</td>
                        <td>{{ $item->produk->nama_produk }}</td>
                        <td>{{ $item->qty_masuk }}</td>
                        <td>{{ 'Rp ' . number_format($item->harga_pembelian, 0, ',', '.') }}</td>
                        <td>{{ $item->created_at->translatedFormat('l, d F Y') }}</td>
                        <td>{{ $item->admin->name }}</td>

                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button type="button"
                                 class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-stok-gudang"
                                 style="width:40px;height:40px;" data-stok-id="{{ $item->id }}"
                                 data-produk-id="{{ $item->produk_id }}" data-produk="{{ $item->produk->nama_produk }}"
                                 data-qty="{{ $item->qty_masuk }}" data-harga="{{ $item->harga_pembelian }}"
                                 data-tenant-id="{{ $tenant->id }}">
                                 <i class="bi bi-pencil p-0"></i>
                              </button>
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-danger btn-md rounded-pill d-flex justify-content-center btn-hapus-stok-gudang"
                                 data-stok-id="{{ $item->id }}" data-id="{{ $item->produk_id }}"
                                 data-produk="{{ $item->produk->nama_produk }}">
                                 <i class="bi bi-trash p-0 pe-2"></i>
                              </button>
                           </div>
                        </td>
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
               <h5>Total : {{$total->total_qty_masuk}} Item</h5>
               <h5>Harga : Rp {{number_format($total->total_harga_pembelian, 0, ',', '.')}}</h5>
            </div>
         </div>
      </div>
   </section>
   {{-- @include('pages.admin.produk.add') --}}

</div>
{{-- @include('pages.admin.stok.add') --}}
@endsection