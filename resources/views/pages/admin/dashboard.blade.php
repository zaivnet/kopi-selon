@extends('layout.admin.app')
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
                     <input type="date" name="date" class="form-control" value="{{ $selectedDate }}"
                        onchange="document.getElementById('filterForm').submit()">
                  </div>

                  <!-- FILTER SHIFT -->
                  <div class="col-md-6">
                     <select name="shift_id" class="form-control"
                        onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Shift</option>
                        @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ $selectedShiftId==$shift->id ? 'selected' : '' }}>
                           {{ $shift->shift }}
                           ({{ $shift->start_time }} - {{ $shift->end_time }})
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
                           <div class="stats-icon green mb-2">
                              <i class="bi bi-cash p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Tunai</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($tunai, 0, ',', '.')}}</h6>
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
                           <div class="stats-icon green mb-2">
                              <i class="bi bi-credit-card p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">QRIS</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($qris, 0, ',', '.')}}</h6>
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
            <div class="col-6 col-md-6">
               <div class="card h-100">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon red mb-2">
                              <i class="bi bi-cash-coin p-0" style="margin-bottom: 20px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class=" col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Harga keluar</h6>
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
                           <div class="stats-icon red mb-2">
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
                                 <td>{{$item->nama_varian}}</td>
                                 <td>{{$item->total_terjual}} item</td>
                              </tr>
                              @empty
                              <tr>
                                 <td colspan="2">Belum ada produk yang terjual.</td>
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
                     <h4 class="card-title">Aktivitas Transaksi</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <tbody>
                              @forelse ($aktivitasTransaksi as $item)
                              <tr>
                                 <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
                                    $item->created_at)->diffForHumans() }}</td>
                                 <td><strong>{{$item->total_item}} item</strong> terjual seharga <strong>{{ 'Rp ' .
                                       number_format($item->total_harga, 0, ',', '.') ?? '-' }} </strong>
                                    @if ($item->nama_pelanggan == '' || $item->nama_pelanggan == '-')
                                    (Kasir: {{$item->nama_kasir}})
                                    @else
                                    ke <strong>{{$item->nama_pelanggan}}</strong> (Kasir: {{$item->nama_kasir}})
                                    @endif
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