@extends('layout.kasir.app')

@section('content')
<div class="page-heading">
   <section class="section">
      <div class="col-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex justify-content-between align-items-center">
                  <div class="fw-bolder">Tanggal <br>
                     {{\Carbon\Carbon::parse($tanggalShift)->format('d-m-Y')}}</div>
                  <div class="fw-bolder text-end">Shift {{$shiftAktif->shift}} <br>{{$shiftAktif->start_time}} -
                     {{$shiftAktif->end_time}}</div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-12">
         <div class="row">
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon purple mb-2 text-center">
                              <i class="bi bi-cart-check p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Produk terjual</h6>
                           <h6 class="font-extrabold mb-0">{{$totalProdukTerjual}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card">
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
               <div class="card">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon red mb-2">
                              <i class="bi bi-cart-x p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Produk Keluar</h6>
                           <h6 class="font-extrabold mb-0">{{$totalProdukRetur}} Item</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
               <div class="card">
                  <div class="card-body px-4 py-4-5">
                     <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                           <div class="stats-icon green mb-2">
                              <i class="bi bi-bag-x p-0" style="margin-bottom: 25px; margin-right: 10px"></i>
                           </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                           <h6 class="text-muted font-semibold">Harga Produk Keluar</h6>
                           <h6 class="font-extrabold mb-0">Rp{{number_format($totalHargaRetur, 0, ',', '.')}}</h6>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-12">
         <div class="row">
            <div class="col-md-6">
               <div class="card">
                  <div class="card-header">
                     <h4 class="card-title">Produk Terlaris</h4>
                  </div>
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="table">
                           <thead>
                              <tr>
                                 <td class="fw-bold">Produk</td>
                                 <td class="fw-bold">Varian</td>
                                 <td class="fw-bold">Terjual</td>
                              </tr>
                           </thead>
                           <tbody>
                              @forelse ($produkTerlaris as $item)
                              <tr>
                                 <td>{{$item->nama_produk}}</td>
                                 <td>{{$item->nama_varian}}</td>
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
               <div class="card">
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

   </section>
</div>
@endsection