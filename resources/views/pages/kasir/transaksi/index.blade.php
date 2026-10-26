@extends('layout.kasir.app')
@section('content')
<style>
   @page {
      size: auto;
      /* Panjang otomatis sesuai konten */
      margin: 0;
      /* Hilangkan margin agar sesuai dengan kertas */
   }

   #exampleModal {
      visibility: hidden;
   }

   #exampleModal .modal-body,
   #exampleModalPelanggan .modal-body {
      max-height: 400px;
      /* sesuaikan */
      overflow-y: auto;
   }

   .product-card.btn-warning {
      background-color: #ff9f43;
      border-color: #ff9f43;
      color: #fff;
   }

   .product-card.btn-warning:hover {
      background-color: #f39c12;
      border-color: #f39c12;
      color: #fff;
   }
</style>
@php
$kode_transaksi = 'TRX' . date('YmdHis');
@endphp

<!-- Button trigger modal -->
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Selesaikan Transaksi</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <div class="modal-body">
            <input type="hidden" name="nama_kasir" id="nama_kasir" value="{{ $nama_kasir }}">
            <iframe id="struk-wrapper" height="400px"></iframe>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
               <i class="bx bx-x d-block d-sm-none"></i>
               <span class="d-none d-sm-block">Batal</span>
            </button>
            <button type="button" class="btn btn-primary ms-1" data-bs-dismiss="modal" id="prosesTransaksi">
               <i class="bx bx-check d-block d-sm-none"></i>
               <span class="d-none d-sm-block">Proses</span>
            </button>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="exampleModalPelanggan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Data Pelanggan</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <div class="modal-body">
            <div class="table-responsive">

               <table class="table table-striped tabel-pelanggan" id="tablePelanggan">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Telepon</th>
                        <th>Poin</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                  </tbody>
               </table>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
               <i class="bx bx-x"></i>
               <span>Kembali</span>
            </button>
         </div>
      </div>
   </div>
</div>

@include('pages.kasir.transaksi.scan')
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
<section class="row">
   <div class="col-12 col-lg-7">
      <div class="card">
         <div class="card-header"><strong>PRODUK</strong></div>
         <div class="card-body">
            <div class="row">
               <div class=" col-12 mb-2">
                  <div class="row g-2">
                     <div class="col-sm-6">
                        <select class="form-select" id="kategori">
                           <option selected value="">Semua Kategori</option>
                           @foreach ($kategori as $kat)
                           <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-sm-6">
                        <div class="input-group mb-3">
                           <button class="input-group-text" id="btnReset">
                              <i class="bi bi-arrow-repeat" style="margin-bottom: 10px !important;"></i>
                           </button>
                           <input type="text" class="form-control" id="search" placeholder="Cari produk..."
                              aria-label="Cari produk" aria-describedby="inputGroup-sizing-sm">
                           <button class="input-group-text" id="btnSearch">
                              <i class="bi bi-search" style="margin-bottom: 10px !important;"></i>
                           </button>
                           <button type="button" class="btn btn-primary" style="height: fit-content" id="scan-button">
                              <i class="bi bi-upc-scan"></i>
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-12">
                  <div class="produk-wrapper">
                  </div>
                  <div class="mt-4 d-flex justify-content-center">
                     <nav aria-label="Page navigation example">
                        <ul class="pagination pagination-produk d-flex flex-wrap justify-content-center">
                        </ul>
                     </nav>
                  </div>
               </div>
            </div>
         </div>

      </div>
   </div>

   <div class="col-12 col-lg-5">
      <form action="{{ route('transaksi.post') }}" method="POST" id="form-transaksi">
         @csrf
         @method('post')
         <div class="card">
            <div class="card-header">
               <input type="hidden" name="kode_transaksi" id="kode-transaksi-input" value="{{ $kode_transaksi }}">
               <strong>KERANJANG</strong> <span class="float-end"><span class="bg-success text-white p-1 rounded"
                     id="kode-transaksi-text">{{
                     $kode_transaksi }}</span></span>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-striped">
                     <thead>
                        <tr>
                           <th scope="col">#</th>
                           <th scope="col">Name</th>
                           <th scope="col">QTY</th>
                           <th scope="col">Price</th>
                           <th scope="col">Total</th>
                        </tr>
                     </thead>
                     <tbody id="table-cart">
                     </tbody>
                  </table>
               </div>
               <div class="mt-2">
                  <button type="button" class="btn btn-secondary btn-sm" id="btn-resetCart">Reset Keranjang</button>
               </div>
            </div>
         </div>
         <div class="card">
            <div class="card-body">
               <label for="nama_pelanggan" style="font-size: 18px;"><strong>Nama Pelanggan</strong></label>
               <div class="input-group mb-3 input-group">
                  <button type="button" class="input-group-text" id="btnResetPelanggan">
                     <i class="bi bi-arrow-repeat" style="margin-bottom: 10px !important;"></i>
                  </button>
                  <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan"
                     placeholder="Tulis manual jika bukan pelanggan" aria-label="Nama Pelanggan"
                     aria-describedby="inputGroup-sizing-sm" autocomplete="off" required>
                  <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="0">
                  <button type="button" class="input-group-text" id="btnCariPelanggan">
                     <i class="bi bi-search" style="margin-bottom: 10px !important;"></i>
                  </button>
               </div>
               <h5 class="mt-2">Total: Rp <span id="total-price">0</span></h5>
               <input type="hidden" name="total_harga" id="total-price-input" value="0">
               <input type="text" name="bayar" id="amount-paid" class="form-control form-control-lg input-number"
                  placeholder="Jumlah bayar" required>
               <div class="mt-2">
                  <input type="hidden" name="kembalian" id="change-amount-input" />
                  <h5>Kembalian : Rp <span id="change-amount">0</span></h5>
               </div>
               <select name="metode_pembayaran" id="metode-bayar" class="form-select mb-2" required>
                  <option value="">Pilih Metode Pembayaran</option>
                  <option value="offline">Tunai</option>
                  <option value="online">QRIS</option>
               </select>
               <button type="submit" class="btn btn-primary btn-block" id="btnPay">BAYAR</button>
            </div>
         </div>
      </form>
   </div>
</section>
@endsection

@section('scripts')

<script>
   $(document).ready(function () {

    $('#btnCariPelanggan').on('click', function () {

        // 🔥 destroy HANYA DataTable pelanggan
        if ($.fn.DataTable.isDataTable('#tablePelanggan')) {
            $('#tablePelanggan').DataTable().destroy();
        }

        $('#tablePelanggan').DataTable({
            processing: true,
            paging: true,
            searching: true,
            ordering: true,
            pageLength: 5,
            ajax: {
                url: "{{ route('ajax.pelanggan.data') }}",
                dataSrc: ''
            },
            columns: [
                {
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'nama' },
                {
                    data: 'telepon',
                    render: data => data ?? '-'
                },
                {
                    data: 'poin',
                    render: data => data ?? 0
                },
                {
                    data: 'id',
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-success tambahPelanggan"
                                data-id_pelanggan="${data}"
                                data-nama="${row.nama}">
                                Pilih
                            </button>
                        `;
                    }
                }
            ]
        });

    });

});
</script>

<script>
   // console.log('Halaman Transaksi Kasir');
   $(document).ready(function() {
      const isKasir = {{ auth()->check() && auth()->user()->isKasir() ? 'true' : 'false' }};
      getProduk();

      function getProduk(page = 1) {
         $.ajax({
            url: "{{ route('ajax.getProduk') }}",
            method: "GET",
            data: {
               page: page,
               search: $('#search').val(),
               kategori_id: $('#kategori').val()
            },
            dataType: "json",
            success: function(response) {
               var produkWrapper = $('.produk-wrapper');
               let data = response.data;
               let link = response.links;
               console.log(data);

               produkWrapper.empty(); // Clear existing products
               // console.log(data, response);
               $.each(data, function(index, produk) {
                  const stok = parseInt(produk.stok) || 0;
                  let btnClass = '';
                  let stokLabel = '';

                  if (isKasir) {
                     if (stok >= 20) {
                        stokLabel = 'Sisa Stok: Tersedia';
                        btnClass = 'btn-success'; // Hijau
                     } else if (stok >= 1) {
                        stokLabel = 'Sisa Stok: Menipis';
                        btnClass = 'btn-warning'; // Kuning/Oranye
                     } else {
                        stokLabel = 'Sisa Stok: Habis';
                        btnClass = 'btn-danger';  // Merah
                     }
                  } else {
                     // Perilaku semula untuk Admin dan role lainnya
                     stokLabel = `Stok: ${produk.stok}`;
                     btnClass = stok > 0 ? 'btn-success' : 'btn-danger';
                  }

                  const disabledAttr = stok > 0 ? '' : 'style="pointer-events:none; opacity:0.6"';
                  let harga_jual = parseFloat(produk.harga_jual);
                  var productCard = `
                     <a href="#" class="select-product" data-id="${produk.produk_varian_id}" data-stok="${produk.stok}" data-produk-toko-id="${produk.produk_toko_id}" data-nama_produk="${produk.nama_produk}"
                     data-nama_varian="${produk.nama_varian}"
                     data-harga_jual="${harga_jual}" ${disabledAttr}>
                     <div class="product-card btn ${btnClass} d-flex flex-column justify-content-center align-items-center">
                        <div class="w-100 flex-grow-0">
                           <i class="bi bi-cart-plus" style="font-size:30px"></i>
                        </div>
                           <h6 class="product-title flex-grow-1 d-flex align-items-center">${produk.nama_varian}</h6>
            
                           <p class="product-price flex-grow-0">Rp${Math.floor(harga_jual).toLocaleString('id-ID')}<br><span
                                 class="product-stok">${stokLabel}</span>
                           </p>
                        </div>
                     </a>
                  `;
                  produkWrapper.append(productCard);
               });

               // Pagination (optional)
               $('.pagination-produk').empty();

               if (link.length > 1) {
                  $.each(link, function(index, pageLink) {

                     // Jika url null → disabled
                     if (!pageLink.url) {
                        $('.pagination-produk').append(`
                           <li class="page-item disabled">
                              <span class="page-link">${pageLink.label}</span>
                           </li>
                        `);
                        return;
                     }

                     var activeClass = pageLink.active ? 'active' : '';

                     var pageItem = `
                        <li class="page-item ${activeClass}">
                           <a class="page-link" href="#" data-url="${pageLink.url}">
                              ${pageLink.label}
                           </a>
                        </li>
                     `;

                     $('.pagination-produk').append(pageItem);
                  });

                  // Handle pagination click
                  $('.pagination-produk .page-link').on('click', function(e) {
                     e.preventDefault();

                     const url = $(this).data('url');
                     if (!url) return;

                     const page = new URL(url).searchParams.get('page');

                     getProduk(page);
                  });
               }
            },
            error: function(xhr, status, error) {
               console.error("Terjadi kesalahan saat mengambil data produk:", error);
            }
         });
      }

      $('#btnSearch').on('click', function() {
         getProduk();
      });

      $('#btnReset').on('click', function() {
         $('#search').val('');
         getProduk();
      });

      $('#kategori').on('change', function() {
         getProduk();
      });

      function getTotalQtyByProdukToko(produkTokoID) {
         let total = 0;
         
         $('#table-cart tr').each(function() {
            if ($(this).data('produk-toko-id') == produkTokoID) {
               total += parseInt($(this).find('.qty').val()) || 0;
            }
         });
         
         return total;
      }

      function getTotalQtyVarianLain(produkTokoID, currentRow) {
         let total = 0;
         
         $('#table-cart tr').each(function () {
            if (
            $(this).data('produk-toko-id') == produkTokoID &&
            !$(this).is(currentRow)
            ) {
            total += parseInt($(this).find('.qty').val()) || 0;
            }
         });
         
         return total;
      }

      

      function updateProductButtonState() {
      
         $('.select-product').each(function () {
      
            let produkTokoID = $(this).data('produk-toko-id');
            let stok = parseInt($(this).data('stok'));
            
            let total = getTotalQtyByProdukToko(produkTokoID);
            
            let sisa = stok - total;

            let btnIncrease = $(`#table-cart tr[data-produk-toko-id="${produkTokoID}"] .btn-increase`);
            
            if (sisa <= 0) { 
               $(this).addClass('disabled') .css({ pointerEvents: 'none' , opacity: 0.4 });
               btnIncrease.prop('disabled', true);
            } else {
               $(this).removeClass('disabled') .css({ pointerEvents: 'auto' , opacity: 1 }); 
               btnIncrease.prop('disabled', false);
            } 
         });
      }

      checkRowTableCart();
      let debounceTimers = {};

      function cekStokProdukToko(produkTokoID, qtyInput) {

         let row = qtyInput.closest('tr');

         if (debounceTimers[produkTokoID]) {
            clearTimeout(debounceTimers[produkTokoID]);
         }

         debounceTimers[produkTokoID] = setTimeout(function () {

            let qtyVarianIni = parseInt(qtyInput.val()) || 0;
            let qtyVarianLain = getTotalQtyVarianLain(produkTokoID, row);

            $.ajax({
               url: "/ajax/cekStokProdukTransaksi/" + produkTokoID,
               method: "GET",
               dataType: "json",
               success: function (response) {

                  let stokInduk = parseInt(response.stok) || 0;
                  let maxQtyVarianIni = stokInduk - qtyVarianLain;

                  if (qtyVarianIni > maxQtyVarianIni) {
                     toastr.warning(
                     'Stok tidak cukup. Maksimal varian ini: ' + maxQtyVarianIni
                     );

                     qtyInput.val(maxQtyVarianIni);
                  }

                  // 🔥 TAMBAHAN PENTING
                  if (maxQtyVarianIni <= 0) {
                     toastr.error('Varian ini tidak memiliki stok tersisa');

                     row.remove(); // ❗ HAPUS DARI CART
                     totalHarga();
                     checkRowTableCart();
                     updateProductButtonState()
                     return;
                  }
               }, 
               error: function () {
                  console.error('Gagal cek stok produk toko ' + produkTokoID);
               }
            });

         }, 250);
      }
      

      $(document).on('click', '.select-product', function (e) {
         e.preventDefault();
      
         var produkID = $(this).data('id');
         let produkTokoID = $(this).data('produk-toko-id');
         var namaProduk = $(this).data('nama_varian');
         var hargaJual = $(this).data('harga_jual');
         let produkVarianID = $(this).data('id');
         let stok = parseInt($(this).data('stok'));
         
         let totalTerpakai = getTotalQtyByProdukToko(produkTokoID);
         
         if (totalTerpakai >= stok) {
            toastr.warning('Stok habis untuk produk ini');
            
            updateProductButtonState();
            return;
         }
         
         let row = $('#table-cart tr[data-varian-id="' + produkVarianID + '"]');
         
         if (row.length > 0) {
            let qtyInput = row.find('.qty');
            qtyInput.val(parseInt(qtyInput.val()) + 1);
         } else {
            $('#table-cart').append(`
            <tr data-varian-id="${produkVarianID}" data-produk-toko-id="${produkTokoID}">
               <td scope="row">
                  <button class="btn btn-danger btn-sm btn-remove">
                     <i class="bi bi-trash"></i>
                  </button>
               </td>
               <td>
                  <input type="hidden" name="produk_varian_id[]" value="${produkID}" />
                  ${namaProduk}
               </td>
               <td>
                  <div class="d-flex align-items-center">
                     <button type="button" class="btn-decrease btn btn-danger btn-sm">-</button>
                     <input class="qty" type="text" name="jumlah[]" value="1" readonly
                           style="width: 30px; text-align: center;" />
                     <button type="button" class="btn-increase btn btn-success btn-sm">+</button>
                  </div>
               </td>
               <td class="harga">
                  <input type="hidden" name="harga_satuan[]" value="${hargaJual}" />
                  Rp ${hargaJual.toLocaleString('id-ID')}
               </td>
               <td>
                  <input type="hidden" name="total_harga_produk[]" class="total-harga-produk" value="${hargaJual}" />
                  Rp <span class='total'>${hargaJual.toLocaleString('id-ID')}</span>
                  </td>
               
            </tr>
            `);
         }
         totalHarga();
         checkRowTableCart();
         clearInputAmountPaid();
         updateProductButtonState();
      });


      $('#table-cart').on('click', '.btn-increase', function() {
         let row = $(this).closest('tr');
         let qtyInput = row.find('.qty');
         let currentQty = parseInt(qtyInput.val());
         qtyInput.val(currentQty + 1);

         let produkID = row.data('id');

         let produkTokoID = row.data('produk-toko-id');
         
         qtyInput.val(currentQty + 1);

         totalHarga();
         checkRowTableCart();
         clearInputAmountPaid();
         updateProductButtonState();
         
         cekStokProdukToko(produkTokoID, qtyInput, currentQty);

         
      });

      $('#table-cart').on('click', '.btn-decrease', function() {
         let row = $(this).closest('tr');
         let qtyInput = row.find('.qty');
         let currentQty = parseInt(qtyInput.val());

         if (currentQty > 1) {
            qtyInput.val(currentQty - 1);
            updateRowTotal(row);
         }
         totalHarga();
         checkRowTableCart();
         clearInputAmountPaid();
         updateProductButtonState();
      });



      $('#btnCariPelanggan').on('click', function(e) {
         e.preventDefault();
         $('#exampleModalPelanggan').modal('show');
      });

      // 🔥 EVENT DELEGATION (PENTING)
      $(document).on('click', '.tambahPelanggan', function(e) {
         e.preventDefault();

         let id_pelanggan = $(this).data('id_pelanggan');
         let nama = $(this).data('nama');

         $('#nama_pelanggan').val(nama).prop('readonly', true);
         $('#id_pelanggan').val(id_pelanggan);

         $('#exampleModalPelanggan').modal('hide');
      });

      $('#btnResetPelanggan').on('click', function(e) {
         e.preventDefault();
         resetPelanggan();
      });

      $('#amount-paid').on('input', function() {
         let totalPriceText = $('#total-price').text().replace(/\./g, '');
         console.log('Total Harga Text:', totalPriceText);
         let totalPrice = parseInt(totalPriceText);
         let amountPaid = parseInt($('#amount-paid').val().replace(/,/g, ''));
         let changeAmount = amountPaid - totalPrice;
         $('#change-amount').text(
         (isNaN(changeAmount) ? 0 : changeAmount).toLocaleString('id-ID')
         );
         $('#change-amount-input').val(changeAmount);

         if (changeAmount < 0) {
            $('#btnPay').prop('disabled', true);
         } else {
            $('#btnPay').prop('disabled', false);
         }

         console.log('amountPaid:', amountPaid);
         console.log('Total Harga:', totalPrice);
      });

      $('#table-cart').on('click', '.btn-remove', function() {
         $(this).closest('tr').remove();
         totalHarga();
         checkRowTableCart();
         clearInputAmountPaid();
         updateProductButtonState();
      });

      $('#form-transaksi').on('submit', function(e) {
         e.preventDefault();
         $('#exampleModal').modal('show');
         getProduk();
         // $('#exampleModal').modal('hide');
         // $('#form-transaksi')[0].submit();
         $.ajax({
            url: "{{ route('transaksi.post') }}",
            method: 'POST',
            data: $('#form-transaksi').serialize(),
            dataType: 'json',
            success: function(res) {
               if (res.status == 'success') {
                  toastr.success('Success : ' + res.msg);
                  let transaksi = res.data.transaksi;
                  let detail_transaksi = res.data.detail_transaksi;
                  let kembali = parseFloat(transaksi.kembalian);
                  let bayar = parseFloat(transaksi.bayar);
                  let metode = '';
                  if(transaksi.metode_pembayaran == 'offline'){
                     metode = 'Tunai';
                  } else {
                     metode = 'QRIS';
                  }
                  
                  let strukHtml = `
                  <h2><?php echo $tenant->name; ?></h2>
                  <p><?php echo $tenant->address; ?></p>

                  <div class="line"></div>

                  <table>
                     <tr>
                        <td>No. Struk</td>
                        <td class="right">${transaksi.kode_transaksi}</td>
                     </tr>
                     <tr>
                        <td>Tanggal</td>
                        <td class="right">${res.data.tanggal_transaksi}</td>
                     </tr>
                     <tr>
                        <td>Kasir</td>
                        <td class="right">${res.data.nama_kasir}</td>
                     </tr>
                  </table>

               <div class="line"></div><table>`;
                  $.each(detail_transaksi, function(index, item) {
                     let namaProduk = item.nama_produk;
                     let qty = item.jumlah;
                     let total = parseFloat(item.total_harga_produk);

                     if (namaProduk !== '') {
                        strukHtml += `
                     <tr class="baris-produk">
                        <td class="left">${namaProduk}</td>
                        <td class="right">x${qty}|</td>
                        <td class="right">Rp ${total.toLocaleString('id-ID')}</td>
                     </tr>`;
                     }
                  })

                  strukHtml += `</table>
                  <div class="line"></div>
                        <table>
                        
                        <tr>
                           <td><strong>Total</strong></td>
                           <td class="right"><strong>Rp ${transaksi.total_harga.toLocaleString('id-ID')}</strong></td>
                        </tr>
                        <tr>
                           <td>Bayar</td>
                           <td class="right">Rp ${bayar.toLocaleString('id-ID')}</td>
                        </tr>
                        <tr>
                           <td><strong>Kembali</strong></td>
                           <td class="right"><strong>Rp ${kembali.toLocaleString('id-ID')}</strong></td>
                        </tr>
                        <tr>
                           <td>Metode Pembayaran</td>
                           <td class="right">${metode}</td>
                        </tr>
                        <tr>
                           <td>Pelanggan</td>
                           <td class="right">${transaksi.nama_pelanggan}</td>
                        </tr>
                  </table>

                  <div class="line"></div>
                  <p class="center fs-sm">${res.data.poin_pelanggan}</p>
                  <div class="line"></div>
                  <p class="center">Terima kasih sudah berkunjung!<br></p>
                  <p class="center">TikTok: <?php echo $tenant->tiktok; ?></p>
                  <p class="center">Instagram: <?php echo $tenant->instagram; ?></p>
                  `;
                  $('#kode-transaksi-input').val(res.kode_transaksi_baru);
                  $('#kode-transaksi-text').text(res.kode_transaksi_baru);
                  printStruk(strukHtml);
                //   doPrintStruk();
                  autoPrintStruk(transaksi.kode_transaksi);
                  console.log(res);
                  clearCart();
                  resetPelanggan();
                  clearInputAmountPaid();
                  console.log('Form transaksi disubmit');
                  getProduk();
               } else if (res.status == 'testing') {
                  console.log(res.data);
               }
            }
         })

         return;

         $('#prosesTransaksi').off('click').on('click', function() {
            $('#form-transaksi')[0].submit();
         });
      });

      $('#btn-resetCart').on('click', function() {
         clearCart();
         checkRowTableCart();
         clearInputAmountPaid();
         updateProductButtonState();
      })

      function printStruk(html) {
         let iframe = document.getElementById('struk-wrapper');
         let doc = iframe.contentWindow.document;

         doc.open();
         doc.write(`
      <html>
      <head>
      <title>Struk</title>
      <style>
         @media print {
               body {
                  margin: 0;
               }
         }

         body {
               width: 43mm;
               font-family: sans-serif;
               font-size: 11px;
         }

         h2 {
               font-size: 14px;
               text-align: center;
               margin: 4px 0;
         }

         p {
               text-align: center;
               margin: 2px 0;
         }

         table {
               width: 100%;
               font-size: 11px;
               border-collapse: collapse;
         }

         td {
               padding: 2px 0;
         }

         .right { text-align: right; }
         .center { text-align: center; }
         .left { text-align: left; }

         .line {
               border-top: 1px dashed #000;
               margin: 6px 0;
         }

         .fs-sm {
            font-size: 8px;
         }
         
         .baris-produk {
            margin: 5px 0;
         }
         @page {
               size: 57mm auto;
               /* Panjang otomatis sesuai konten */
               margin: 0;
               /* Hilangkan margin agar sesuai dengan kertas */
         }
      </style>
      </head>
      <body>
         ${html}
      </body>
      </html>
      `);
         doc.close();
      }

      function doPrintStruk() {
         let iframe = document.getElementById('struk-wrapper');

         if (!iframe || !iframe.contentWindow) {
            alert('Struk belum siap');
            return;
         }

         // iframe.contentWindow.focus();
         iframe.contentWindow.print();

         setTimeout(() => {
            $('#exampleModal').modal('hide');
         }, 500);
      }
    
      function autoPrintStruk(kodeTransaksi) {
         const isMobile = window.innerWidth < 768; if (isMobile) { 
            printStrukMobile(kodeTransaksi); 
         } else { 
            printStrukPC(); 
         } 
      }

      function printStrukPC() {
         const iframe = document.getElementById('struk-wrapper');

         if (!iframe || !iframe.contentWindow) {
            alert('Struk belum siap');
            return;
         }

         iframe.contentWindow.focus();
         iframe.contentWindow.print();

         setTimeout(() => {
            $('#exampleModal').modal('hide');
         }, 500);
      }

      async function printStrukMobile(transaksiId) {
         try {
            const res = await fetch(`/print/struk/base64/${transaksiId}`);
            if (!res.ok) throw new Error('Response error');
            
            const base64 = await res.text();
            
            // WAJIB encode
            const encoded = encodeURIComponent(base64);
            
            setTimeout(() => {
               window.location.href = `rawbt://base64,${encoded}`;
            }, 100);
            
            setTimeout(() => {
               $('#exampleModal').modal('hide');
            }, 500);
            
         } catch (err) {
            alert('Gagal cetak struk (HP)');
            console.error(err);
         }
      }

      function totalHarga() {
         let total = 0;

         $('#table-cart tr').each(function() {
            let row = $(this);

            // Ambil harga asli
            let hargaText = row.find('.harga').text().replace(/[^0-9]/g, '');
            let harga = parseInt(hargaText);

            // Ambil qty
            let qty = parseInt(row.find('.qty').val());

            // Hitung total per item
            let totalPerItem = harga * qty;

            // Update tampilan total per baris
            row.find('.total').text(totalPerItem.toLocaleString('id-ID'));

            // Tambah ke total keseluruhan
            total += totalPerItem;
         });

         $('#total-price').text(total.toLocaleString('id-ID'));
         $('#total-price-input').val(total);
      }


      function updateRowTotal(row) {
         let qty = parseInt(row.find('.qty').val());

         // Ambil hanya harga dalam baris itu
         let hargaText = row.find('.harga').text().replace(/[^0-9]/g, '');
         let harga = parseInt(hargaText);

         let total = qty * harga;

         row.find('.total-harga-produk').val(total);
         row.find('.total').text(total.toLocaleString('id-ID'));
      }

      function checkRowTableCart() {
         let rowCount = $('#table-cart tr').length;

         console.log('Cek input pembayaran : ' + $('#amount-paid').val());
         if (rowCount === 0) {
            $('#total-price').text('0');
            $('#change-amount').text('0');

            $('#btnPay').prop('disabled', true);
            $('#amount-paid').val('').prop('disabled', true);

         } else {
            $('#btnPay').prop('disabled', false);
            $('#amount-paid').prop('disabled', false);
         }
      }

      function clearInputAmountPaid() {
         $('#amount-paid').val('');
         $('#change-amount').text('0');
         $('#metode-bayar').val('');
      }

      function resetPelanggan() {
         $('#nama_pelanggan').val('').prop('readonly', false);
         $('#id_pelanggan').val('0');
      }

      function clearCart() {
         $('#table-cart').empty();
         $('#total-price').text('0');
         $('#total-price-input').val('');
         $('#btnPay').prop('disabled', true);
      }
   });
</script>
@endsection