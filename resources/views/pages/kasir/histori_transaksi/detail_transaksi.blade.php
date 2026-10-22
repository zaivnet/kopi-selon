@extends('layout.kasir.app')
@section('content')
@foreach (['success', 'error', 'warning', 'info'] as $type)
@if (session()->has($type))
<div class="alert alert-{{ $type == 'error' ? 'danger' : $type }}">
   {{ session($type) }}
</div>
@endif
@endforeach

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
   data-update-template="{{ url('/detailTransaksi/updateProdukTransaksi/:id')}}">
   <div class=" modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Pembatalan dan Retur Produk</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="" id="formUpdateJumlahProduk" method="post">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <div class="alert bg-light-primary border-1 border-primary d-flex align-items-center text-sm">
                  Request pembatalan & retur hanya bisa dilakukan 1x di setiap item. Pastikan kasir membuat request yg
                  benar!
               </div>
               <table style="font-size:18px;">
                  <tr>
                     <th>Nama Produk</th>
                     <td class="px-2">:</td>
                     <td id="nama_produk_text"></td>
                  </tr>
                  <tr>
                     <th>Jumlah</th>
                     <td class="px-2">:</td>
                     <td><span id="jumlah_text"></span><span id="update_jumlah_text"></span></td>
                  </tr>
                  <tr>
                     <th>Harga Satuan</th>
                     <td class="px-2">:</td>
                     <td id="harga_satuan_text"></td>
                  </tr>
                  <tr>
                     <th>Total Harga Produk</th>
                     <td class="px-2">:</td>
                     <td><span id="total_harga_produk_text"></span><span id="update_total_harga_produk_text"></span>
                     </td>
                  </tr>
               </table>
               <div class="d-flex mt-2">
                  <!-- <a href="" class="btn-kurangi btn btn-danger">Kurangi Jumlah</a> -->
                  <button type="button" class="btn-kurangi btn btn-danger"><i class="bi bi-dash"></i></button>
                  <input class="jumlah_val form-control" type="number" name="jumlah" min="0" readonly
                     style="font-size: 18px;" />
                  <button type="button" class="btn-reset btn btn-secondary"><i
                        class="bi bi-arrow-clockwise"></i></button>
                  <!-- <a href="" class="btn-reset btn btn-secondary">Ulangi / Reset</a> -->
               </div>
               <div class="form-group mt-2 d-none">
                  <label for="jumlah_retur">Jumlah Yang Ingin Dikurangi</label>
                  <input type="number" name="jumlah_retur" id="jumlah_retur" readonly class="form-control" required />
               </div>

               <fieldset class="form-group mt-2">
                  <label for="type">Jenis Request</label>
                  <select class="form-select" id="type" name="type" required>
                     <option value="" disabled selected>-- Jenis Request --</option>
                     <option value="pembatalan">Pembatalan</option>
                     <option value="retur">Retur</option>
                  </select>
               </fieldset>
               <div class="form-group mt-2">
                  <label for="keterangan">Keterangan</label>
                  <input type="text" name="keterangan" id="keterangan" class="form-control"
                     placeholder="Masukkan keterangan retur produk" required />
               </div>
            </div>
            <div class="modal-footer">
               <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">
                  <span class="d-block">Batal</span>
               </button>
               <button type="submit" class="btn btn-primary ms-1">
                  <span class="d-block">Update</span>
               </button>
            </div>
         </form>
      </div>
   </div>
</div>


{{-- <div class="modal fade" id="exampleModalPrintStruk" tabindex="-1" aria-labelledby="exampleModalLabel"
   aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Form Return Produk</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <div class="modal-body">
            <center>
               <iframe id="struk-wrapper" height="500px"></iframe>
            </center>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
               <i class="bx bx-x d-block d-sm-none"></i>
               <span class="d-none d-sm-block">Batal</span>
            </button>
            <button type="submit" class="btn btn-primary ms-1">
               <i class="bx bx-check d-block d-sm-none"></i>
               <span class="d-none d-sm-block">Update</span>
            </button>
         </div>
      </div>
   </div>
</div> --}}


@if(count($validasi_retur) > 0)
<div class="card">
   <div class="card-body">
      <h4 class="blink-retur">Request Pembatalan & Retur</h4>
      <div class="table-responsive">
         <table class="table table-grid">
            <thead>
               <tr>
                  <th>No</th>
                  <th>Nama Produk</th>
                  <th>Total Harga</th>
                  <th>Jenis Request</th>
                  <th>Status Validasi</th>
                  <th>Keterangan</th>
               </tr>
            </thead>
            <tbody>
               @foreach($validasi_retur as $row)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->nama_varian }} <span class="badge bg-danger">{{ $row->jumlah_item }}</span></td>
                  <td>{{ 'Rp ' . number_format($row->harga_item, 0, ',', '.') }}</td>
                  <td>{{Str::title($row->type)}}</td>
                  <td>
                     @if($row->status_validasi == 'pending')
                     <span class="badge bg-secondary">Pending</span>
                     @elseif($row->status_validasi == 'diterima')
                     <span class="badge bg-success">Diterima</span>
                     @elseif($row->status_validasi == 'ditolak')
                     <span class="badge bg-danger">Ditolak</span>
                     @endif
                  </td>
                  <td>{{ $row->keterangan }}</td>

               </tr>
               @endforeach
         </table>
      </div>
   </div>
</div>
@endif
<h4>Detail Transaksi</h4>
<div class="card">
   <div class="card-body">
      {{-- <div class="mb-2 d-flex justify-content-between">

         <button type="button" class="btn btn-success" id="print-struk" data-id_transaksi="{{ $transaksi->id }}"><i
               class="bi bi-printer"></i> Print Struk</button>
      </div> --}}
      <div class="row d-none d-lg-flex mb-3">
         <div class="col-md-4 flex-grow-1">
            <table>
               <tr>
                  <th>Kode Transaksi</th>
                  <td class="px-2">:</td>
                  <td id="kodeTransaksiText">{{ $transaksi->kode_transaksi }}</td>
               </tr>
               <tr>
                  <th>Kasir</th>
                  <td class="px-2">:</td>
                  <td id="kasirText">{{ $kasir->name }}</td>
               </tr>
               <tr>
                  <th>Pelanggan</th>
                  <td class="px-2">:</td>
                  <td id="pelangganText">{{ $transaksi->nama_pelanggan}}</td>
               </tr>
            </table>
         </div>
         <div class="col-md-3 flex-grow-0 flex-shrink-1">
            <table>
               <tr>
                  <th>Total Item</th>
                  <td class="px-2">:</td>
                  <td>{{ $transaksi->total_item }}</td>
               </tr>
               <tr>
                  <th>Total Harga</th>
                  <td class="px-2">:</td>
                  <td id="totalHargaText">{{ 'Rp ' . number_format($transaksi->total_harga, 0, ',', '.') }}
                  </td>
               </tr>
               <tr>
                  <th>Bayar</th>
                  <td class="px-2">:</td>
                  <td id="bayarText">{{ 'Rp ' . number_format($transaksi->bayar, 0, ',', '.') }}</td>
               </tr>
            </table>
         </div>
         <div class="col-md-5 flex-grow-1">
            <table>
               <tr>
                  <th>Kembalian</th>
                  <td class="px-2">:</td>
                  <td id="kembalianText">{{ 'Rp ' . number_format($transaksi->kembalian, 0, ',', '.') }}</td>
               </tr>
               <tr>
                  <th>Metode Pembayaran</th>
                  <td class="px-2">:</td>
                  <td id="metodePembayaranText">
                     @if ($transaksi->metode_pembayaran == 'offline')
                     Tunai
                     @else
                     QRIS
                     @endif
                  </td>
               </tr>
               <tr>
                  <th>Tanggal</th>
                  <td class="px-2">:</td>
                  <td id="tanggalTransaksiText">
                     {{ $tanggal_transaksi }}
                  </td>
               </tr>
            </table>
         </div>
      </div>

      <div class="row d-flex d-md-block d-lg-none mb-3">
         <div class="col-md-4 flex-grow-1">
            <table>
               <tr>
                  <th>Kode Transaksi</th>
                  <td class="px-2">:</td>
                  <td id="kodeTransaksiText">{{ $transaksi->kode_transaksi }}</td>
               </tr>
               <tr>
                  <th>Kasir</th>
                  <td class="px-2">:</td>
                  <td id="kasirText">{{ $kasir->name }}</td>
               </tr>
               <tr>
                  <th>Pelanggan</th>
                  <td class="px-2">:</td>
                  <td id="pelangganText">{{ $transaksi->nama_pelanggan}}</td>
               </tr>
               <tr>
                  <th>Total Item</th>
                  <td class="px-2">:</td>
                  <td>{{ $transaksi->total_item }}</td>
               </tr>
               <tr>
                  <th>Total Harga</th>
                  <td class="px-2">:</td>
                  <td id="totalHargaText">{{ 'Rp ' . number_format($transaksi->total_harga, 0, ',', '.') }}
                  </td>
               </tr>
               <tr>
                  <th>Bayar</th>
                  <td class="px-2">:</td>
                  <td id="bayarText">{{ 'Rp ' . number_format($transaksi->bayar, 0, ',', '.') }}</td>
               </tr>
               <tr>
                  <th>Kembalian</th>
                  <td class="px-2">:</td>
                  <td id="kembalianText">{{ 'Rp ' . number_format($transaksi->kembalian, 0, ',', '.') }}</td>
               </tr>
               <tr>
                  <th>Metode Pembayaran</th>
                  <td class="px-2">:</td>
                  <td id="metodePembayaranText">
                     @if ($transaksi->metode_pembayaran == 'offline')
                     Tunai
                     @else
                     QRIS
                     @endif
                  </td>
               </tr>
               <tr>
                  <th>Tanggal</th>
                  <td class="px-2">:</td>
                  <td id="tanggalTransaksiText">
                     {{ $tanggal_transaksi }}
                  </td>
               </tr>
            </table>
         </div>
      </div>
      <div class="table-responsive">
         <table class="table table-striped">
            <thead>
               <tr>
                  <th>No</th>
                  <th>Nama Produk</th>
                  <th>Jumlah Item</th>
                  <th>Harga Satuan</th>
                  <th>Total Harga</th>
                  <th>Pembatalan & Retur</th>
               </tr>
            </thead>
            <tbody>
               @php
               $total_harga = 0;
               @endphp
               @foreach($detail_transaksi as $row)
               <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->nama_varian }}</td>
                  <td>{{ $row->jumlah }}</td>
                  <td>{{ 'Rp ' . number_format($row->harga_satuan, 0, ',', '.') }}</td>
                  <td>
                     {{ 'Rp ' . number_format($row->total_harga_produk, 0, ',', '.') }}
                     @php
                     $total_harga+=$row->total_harga_produk;
                     @endphp
                  </td>
                  <td>
                     
                        <button type="button"
                           data-id_detail_transaksi="{{ $row->id_detail_transaksi }}"
                           data-nama_produk="{{ $row->nama_varian }}" data-jumlah="{{ $row->jumlah }}"
                           data-harga_satuan="{{ $row->harga_satuan }}"
                           data-total_harga_produk="{{ $row->total_harga_produk }}"
                           class="btn btn-sm btn-warning btn-batal-dan-retur">
                           Batal & Retur
                        </button>
                     
                  </td>

               </tr>
               @endforeach
            </tbody>
         </table>
         <div class="d-flex justify-content-end">
            <p style="font-size: 30px;"><strong>TOTAL :</strong> {{ 'Rp ' . number_format($total_harga, 0, ',', '.') }}
            </p>
         </div>
      </div>
      <a href="{{ route('kasir.historiTransaksi') }}" class="btn btn-secondary btn-sm mt-3"> <i
            class="bi bi-arrow-left-square"></i> Kembali</a>
   </div>
</div>

@endsection

{{-- @section('scripts')
<script>
   $(document).ready(function() {
      

      // $('#print-struk').on('click', function() {
      //    $('#exampleModalPrintStruk').modal('show');
      //    let id_transaksi = $(this).data('id_transaksi');
      //    $.ajax({
      //       url: '/ajax/getDetailTransaksi/' + id_transaksi,
      //       method: 'GET',
      //       dataType: 'json',
      //       success: function(res) {
      //          //   console.log(res);
      //          let detail_transaksi = res.data;
      //          let strukHtml = `
      //               <h2><?php echo $tenant->name; ?></h2>
      //               <p><?php echo $tenant->address; ?></p>

      //               <div class="line"></div>

      //               <table>
      //                   <tr>
      //                       <td>No. Struk</td>
      //                       <td class="right">${$('#kodeTransaksiText').text()}</td>
      //                   </tr>
      //                   <tr>
      //                       <td>Tanggal</td>
      //                       <td class="right">${$('#tanggalTransaksiText').text()}</td>
      //                   </tr>
      //                   <tr>
      //                       <td>Kasir</td>
      //                       <td class="right">${$('#kasirText').text()}</td>
      //                   </tr>
      //               </table>

      //               <div class="line"></div><table>`;
      //          $.each(detail_transaksi, function(index, item) {
      //             let namaProduk = item.nama_produk;
      //             let qty = item.jumlah;
      //             let total = parseFloat(item.total_harga_produk);

      //             if (namaProduk !== '') {
      //                strukHtml += `
      //                       <tr>
      //                           <th class="left">${namaProduk}</th>
      //                           <th class="right">x${qty}|</th>
      //                           <th class="right">${(total == 0 ? 'Diretur' : total.toLocaleString('id-ID'))}</th>
      //                       </tr>`;
      //             }
      //          })

      //          strukHtml += `</table>
      //                   <div class="line"></div>
      //                       <table>
      //                       <tr>
      //                           <td><strong>Pelanggan</strong></td>
      //                           <td class="right"><strong>${$('#pelangganText').text()}</strong></td>
      //                       </tr>
      //                       <tr>
      //                           <td><strong>Total</strong></td>
      //                           <td class="right"><strong>${$('#totalHargaText').text()}</strong></td>
      //                       </tr>
      //                       <tr>
      //                           <td>Bayar</td>
      //                           <td class="right">${$('#bayarText').text()}</td>
      //                       </tr>
      //                       <tr>
      //                           <td><strong>Kembali</strong></td>
      //                           <td class="right"><strong>${$('#kembalianText').text()}</strong></td>
      //                       </tr>
      //                       <tr>
      //                           <td>Metode Pembayaran</td>
      //                           <td class="right">${$('#metodePembayaranText').text()}</td>
      //                       </tr>
      //                   </table>

      //                   <div class="line"></div>
      //                   <p class="center">🎁 ${res.poin_pelanggan} 🎁</p>
      //                   <div class="line"></div>
      //                   <p class="center">Terima kasih sudah berkunjung!<br>✨ Sampai jumpa lagi ✨</p>
      //                   <p class="center">TikTok : <?php echo $tenant->tiktok ?></p>
      //                   <p class="center">Instagram : <?php echo $tenant->instagram ?></p>
      //                   `;

      //          // $('#struk-wrapper').html(strukHtml);
      //          printStruk(strukHtml);
      //       }
      //    })
      // })

      // function printStruk(html) {
      //    let iframe = document.getElementById('struk-wrapper');
      //    let doc = iframe.contentWindow.document;

      //    doc.open();
      //    doc.write(`
      //    <html>
      //    <head>
      //    <title>Struk</title>
      //    <style>
      //          @media print {
      //             body {
      //                margin: 0;
      //             }
      //          }

      //          body {
      //             width: 210px;
      //             font-family: monospace;
      //             font-size: 11px;
      //          }

      //          h2 {
      //             font-size: 14px;
      //             text-align: center;
      //             margin: 4px 0;
      //          }

      //          p {
      //             text-align: center;
      //             margin: 2px 0;
      //          }

      //          table {
      //             width: 100%;
      //             font-size: 11px;
      //             border-collapse: collapse;
      //          }

      //          td {
      //             padding: 2px 0;
      //          }

      //          .right { text-align: right; }
      //          .center { text-align: center; }
      //          .left { text-align: left; }

      //          .line {
      //             border-top: 1px dashed #000;
      //             margin: 6px 0;
      //          }
      //    </style>
      //    </head>
      //    <body onload="window.print()">
      //       ${html}
      //    </body>
      //    </html>
      //    `);
      //    doc.close();
      // }

   })
</script>
@endsection --}}