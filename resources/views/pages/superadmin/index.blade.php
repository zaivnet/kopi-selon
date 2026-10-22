@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <h3 class="mt-10">Dashboard</h3>
   </div>
   <section class="section">
      <div class="col-12">
         <div class="card mb-4">
            <div class="card-body">
               <form method="GET" id="filterForm" class="row g-2">

                  <!-- FILTER TANGGAL -->
                  <div class="col-md-6">
                     <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" required
                        onchange="document.getElementById('filterForm').submit()">
                  </div>

                  <div class="col-md-6">
                     <select name="tenant_id" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('tenant_id', 'all' )==='all' ? 'selected' : '' }}>
                           Semua Toko
                        </option>

                        @foreach ($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id')==$t->id ? 'selected' : '' }}>
                           {{ $t->name }}
                        </option>
                        @endforeach
                     </select>
                  </div>

               </form>
            </div>
         </div>

      </div>
      <div class="col-12">
         <div class="row d-flex g-3 mb-4">
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon purple mb-2 text-center">
                              <i class="bi bi-cart-check p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Produk terjual</h6>
                           <h6 class="font-extrabold mb-0">{{$totalProdukTerjual}} item</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon blue mb-2">
                              <i class="bi bi-cash-coin p-0" style="margin-bottom: 20px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Pendapatan</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($pendapatan, 0, ',', '.')}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon red mb-2">
                              <i class="bi bi-cart-x-fill p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Produk keluar</h6>
                           <h6 class="font-extrabold mb-0">{{$totalProdukRetur}} item</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon red mb-2">
                              <i class="bi bi-cash-coin p-0" style="margin-bottom: 20px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Harga produk keluar</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($totalHargaRetur, 0, ',', '.')}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon green mb-2">
                              <i class="bi bi-cash-stack p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Pengeluaran toko</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($pengeluaran, 0, ',', '.')}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon red mb-2">
                              <i class="bi bi-wallet2 p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Laba bersih</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($laba, 0, ',', '.')}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
   <section class="section">
      <div class="col-12">
         <div class="row mb-4 d-flex g-3">
            <div class="col-md-6">
               <div class="card h-100">
                  <div class="card-header">
                     <h4 class="card-title">Produk Terlaris</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <thead>
                              <tr>
                                 <td class="fw-bold">Produk</td>
                                 <td class="fw-bold">Terjual</td>
                              </tr>
                           </thead>
                           <tbody>
                              @forelse ($produkTerlaris as $item)
                              <tr>
                                 <td>{{$item->nama_produk}}</td>
                                 <td>{{$item->total_terjual}} item</td>
                              </tr>
                              @empty
                              <tr>
                                 <td colspan="3">Belum ada produk yang terjual.</td>
                              </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="card h-100">
                  <div class="card-header">
                     <h4 class="card-title">Produk Keluar</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <tbody>
                              @forelse ($produkKeluarToko as $item)
                              <tr>
                                 <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal)->diffForHumans() }}
                                 </td>
                                 <td>
                                    <strong>{{$item->total_keluar . ' '. $item->nama_produk}}</strong> seharga
                                    <strong>{{ 'Rp' .
                                       number_format($item->total_harga, 0, ',', '.') ?? '-' }}</strong> keluar dari
                                    <strong>{{$item->toko}}</strong> (Keterangan: {{$item->keterangan}})

                                 </td>
                              </tr>
                              @empty
                              <tr>
                                 <td colspan="2">Belum ada produk keluar toko.</td>
                              </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="card h-100">
                  <div class="card-header">
                     <h4 class="card-title">Aktivitas Stok Gudang Masuk</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <tbody>
                              @forelse ($aktivitasGudangMasuk as $item)
                              <tr>
                                 <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
                                    $item->created_at)->diffForHumans() }}</td>
                                 <td><strong>{{$item->user}}</strong> menambahkan <strong>{{$item->qty_masuk . ' ' .
                                       $item->nama_produk}}</strong> ke <strong>{{$item->gudang}}</strong> dengan harga
                                    pembelian
                                    <strong>{{ 'Rp ' .
                                       number_format($item->harga_pembelian, 0, ',', '.') ?? '-' }} </strong>
                                 </td>
                              </tr>
                              @empty
                              <tr>
                                 <td colspan="2">Belum ada aktivitas.</td>
                              </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="card h-100">
                  <div class="card-header">
                     <h4 class="card-title">Aktivitas Stok Gudang Keluar</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <tbody>
                              @forelse ($aktivitasGudangKeluar as $item)
                              <tr>
                                 <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
                                    $item->created_at)->diffForHumans() }}</td>
                                 <td><strong>{{$item->qty_keluar.' '.$item->nama_produk}}</strong> keluar dari
                                    <strong>{{$item->gudang}}</strong> (Keterangan: {{$item->keterangan}})
                                 </td>
                              </tr>
                              @empty
                              <tr>
                                 <td colspan="2">Belum ada aktivitas transaksi.</td>
                              </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="card">
         <div class="card-header">
            <h4 class="card-title">Pendapatan dan Pengeluaran <span class="fw-light">| {{
                  \Carbon\Carbon::now()->translatedFormat('F Y') }}</span></h4>
         </div>
         <div class="card-body table-responsive">
            <table>
               <div id="chartPendapatan"></div>
            </table>
         </div>
      </div>
   </section>
</div>

<script>
   window.chartBulanan = {
      labels: @json($labels),
      pendapatan: @json($seriesPendapatan),
      pengeluaran: @json($seriesPengeluaran)
   };
</script>
@endsection