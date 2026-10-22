// toastr
document.addEventListener("DOMContentLoaded", function () {
   // Fungsi untuk menampilkan toastr berdasarkan tipe pesan
   function showToastr(type, message, title) {
      if (!message) return; // Cegah toastr kosong

      toastr[type](message, title, {
         closeButton: true,
         progressBar: true,
         positionClass: "toast-top-right",
         timeOut: 2000, // Durasi notifikasi dalam ms
         extendedTimeOut: 1000,
         showMethod: "fadeIn",
         hideMethod: "fadeOut",
      });

      // Simpan state agar toastr tidak muncul lagi setelah reload
      sessionStorage.setItem("toastr" + type, "shown");
   }

   // Cek apakah toastr success atau error sudah pernah ditampilkan
   if (!sessionStorage.getItem("toastrSuccess") && window.toastrSuccess) {
      showToastr("success", window.toastrSuccess, "Success");
   }

   if (!sessionStorage.getItem("toastrError") && window.toastrError) {
      showToastr("error", window.toastrError, "Error");
   }

   // 🔥 Tampilkan semua pesan error validasi
   if (window.validationErrors && window.validationErrors.length > 0) {
      window.validationErrors.forEach(function (msg) {
         showToastr("error", msg, "Error");
      });
   }
});


// toggle theme icon
$(document).ready(function () {
   $('#toggle-dark').click(function () {
      let currentTheme = localStorage.getItem('theme');

      // Toggle nilai theme
      if (currentTheme === 'dark') {

         $('#theme-icon-wrapper').html(`<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img"
            class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
            <path fill="currentColor"
               d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
            </path>
         </svg>`);
      } else {
         $('#theme-icon-wrapper').html(`<svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
            role="img" class="iconify iconify--system-uicons" width="20" height="20" preserveAspectRatio="xMidYMid meet"
            viewBox="0 0 21 21">
            <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
               <path
                  d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                  opacity=".3"></path>
               <g transform="translate(-210 -1)">
                  <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                  <circle cx="220.5" cy="11.5" r="4"></circle>
                  <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2">
                  </path>
               </g>
            </g>
         </svg>`);
      }
   });
});
// end toggle theme icon

function formatRupiah(angka) {
   if (!angka) return "";

   // Konversi ke string, hapus non-digit
   angka = angka.toString().replace(/\D/g, "");

   // Tambah titik pemisah ribuan
   return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// format currency
function formatCurrency(input) {
   // Ambil nilai input, hapus semua karakter selain angka
   let value = input.value.replace(/\D/g, '');

   // Ubah ke format Rupiah (Rp)
   let formatted = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
   }).format(value);

   // Hapus simbol 'Rp' bawaan dari formatCurrency agar tidak double
   input.value = formatted.replace("Rp", "").trim();
}


// $(document).ready(function () {
//    $(document).on('click', '.btn-status', function (e) {
//       e.preventDefault();
//       e.stopPropagation();
//    });
// });

// $(document).ready(function () {
//    $(document).on('click', '.btn-hapus-toko', function (e) {
//       e.preventDefault();
//       e.stopPropagation();
//    });
// });

/** JS FOR EDIT KATEGORI */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-kategori');
   if ($modal.length === 0) return;

   const $form = $('#formEditKategori');
   const $idField = $('#id-kategori');
   const $kategoriField = $('#kategori');


   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   // Event klik tombol edit
   $('.btn-edit-kategori').on('click', function () {
      const id = $(this).data('id');
      const kategori = $(this).data('kategori');

      if (!id || !kategori) {
         console.error('Data tidak ditemukan pada tombol edit.');
         return;
      }

      // Isi field modal
      $idField.val(id);
      $kategoriField.val(kategori);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });
});

/** JS FOR HAPUS KATEGORI */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-kategori');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $('.btn-hapus-kategori').on('click', function () {
      const id = $(this).data('id');
      const kategori = $(this).data('kategori');

      if (!id || !kategori) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Hapus kategori ' + kategori + '?')

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus-kategori');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR EDIT PRODUK GUDANG*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-produk');
   if ($modal.length === 0) return;

   const $form = $('#formEditProduk');
   const $idField = $('#id-produk');
   const $produkField = $('#edit-nama-menu');
   const $kategoriField = $('#kategori');
   // const $hargaJualField = $('#edit-harga-jual');


   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   // Event klik tombol edit
   $('.btn-edit-produk').on('click', function () {

      // console.log("=== DEBUG BUTTON EDIT ===");
      // console.log("ID:", $(this).data('id'));
      // console.log("Produk:", $(this).data('produk'));
      // console.log("Kategori:", $(this).data('kategori'));
      // console.log("Harga:", $(this).data('harga'));

      const tenantId = $(this).data('tenant-id');
      const produkId = $(this).data('produk-id');
      const produk = $(this).data('produk');
      const kategori = $(this).data('kategori');
      // const harga = $(this).data('harga');

      if (!tenantId || !produkId || !produk || !kategori) {
         console.error('Data tidak ditemukan pada tombol edit.');
         return;
      }

      // Isi field modal
      // $idField.val(id);
      $produkField.val(produk);
      $kategoriField.val(kategori).change();
      // $hargaJualField.val(formatRupiah(harga));

      // Ganti action form
      const actionUrl = templateUrl
         .replace(':tenantId', tenantId)
         .replace(':produkId', produkId);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });
});

/** JS FOR HAPUS PRODUK GUDANG*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-produk');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $(document).on('click', '.btn-hapus-produk', function () {
      const id = $(this).data('id');
      const produk = $(this).data('produk');

      if (!id || !produk) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Hapus ' + produk + '?');

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus-produk');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR ADD STOK MASUK GUDANG*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-add-stok-gudang');
   // console.log($modal)
   if ($modal.length === 0) return;

   $(document).on('click', '.btn-tambah-stok-gudang', function () {
      const id = $(this).data('id');
      const produk = $(this).data('produk');

      if (!id || !produk) {
         console.error('Data tidak ditemukan pada tombol tambah stok.');
         return;
      }

      const idp = $('#id_produk').val(id);
      const prd = $('.produk-info').text(produk);
      // console.log(idp, prd)
   })
});

/** JS FOR EDIT STOK MASUK GUDANG*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-stok-gudang');
   // console.log($modal)
   if ($modal.length === 0) return;

   $(document).on('click', '.btn-edit-stok-gudang', function () {

      const stokId = $(this).data('stok-id');
      const produkId = $(this).data('produk-id');
      const produk = $(this).data('produk');
      const qty = $(this).data('qty');
      const harga = $(this).data('harga');
      const tenantId = $(this).data('tenant-id');

      // info produk
      $('.produk-info').text(`Produk: ${produk}`);

      // isi input
      $('#id_produk-edit').val(produkId);
      $('#qty_masuk-edit').val(qty);
      $('#harga_beli-edit').val(formatRupiah(harga));

      // set action form
      let actionTemplate = $('#modal-edit-stok-gudang').data('update-template');
      let actionUrl = actionTemplate
         .replace(':tenantId', tenantId)
         .replace(':produkId', stokId);

      $('#form-edit-stok-gudang').attr('action', actionUrl);

      // tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });
});

/** JS FOR HAPUS STOK GUDANG MASUK */
$(document).ready(function () {

   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-stok-gudang');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus di tabel
   $(document).on('click', '.btn-hapus-stok-gudang', function () {
      const stokId = $(this).data('stok-id');
      const produk = $(this).data('produk');

      if (!stokId || !produk) {
         console.error('Data stok atau produk tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      // Isi teks konfirmasi
      $modalDelete.find('.confirm')
         .text('Yakin ingin menghapus histori stok masuk produk "' + produk + '" ?');

      // Tombol konfirmasi hapus
      const $btnConfirmDelete = $('#btn-confirm-hapus-stok-gudang');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');

      // Set stokId ke tombol konfirmasi
      $btnConfirmDelete.data('stok-id', stokId);

      // Hindari double binding
      $btnConfirmDelete.off('click').on('click', function (e) {
         e.preventDefault();

         const id = $(this).data('stok-id');
         const href = templateUrl.replace(':id', id);

         window.location.href = href;
      });
   });

});


/** JS FOR ADD STOK KELUAR GUDANG*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-add-stok-gudang-keluar');
   // console.log($modal)
   if ($modal.length === 0) return;

   $('.btn-tambah-stok-gudang-keluar').on('click', function () {
      const id = $(this).data('id');
      const produk = $(this).data('produk');

      if (!id || !produk) {
         console.error('Data tidak ditemukan pada tombol tambah stok.');
         return;
      }

      const idp = $('#id_produk').val(id);
      const prd = $('.produk-info').text(produk);
      // console.log(idp, prd)
   })
});

/** JS FOR HAPUS STOK GUDANG KELUAR */
$(document).ready(function () {

   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-stok-gudang-keluar');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus di tabel
   $(document).on('click', '.btn-hapus-stok-gudang-keluar', function () {

      const stokId = $(this).data('stok-id');
      const produk = $(this).data('produk');

      if (!stokId || !produk) {
         console.error('Data stok atau produk tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      // Isi teks konfirmasi
      $modalDelete.find('.confirm')
         .text('Yakin ingin menghapus histori stok keluar produk "' + produk + '" ?');

      // Tombol konfirmasi hapus
      const $btnConfirmDelete = $('#btn-confirm-hapus-stok-gudang-keluar');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');

      // Set stokId ke tombol konfirmasi
      $btnConfirmDelete.data('stok-id', stokId);

      // Hindari double binding
      $btnConfirmDelete.off('click').on('click', function (e) {
         e.preventDefault();

         const id = $(this).data('stok-id');
         const href = templateUrl.replace(':id', id);

         window.location.href = href;
      });
   });

});

// TOKO

/** JS FOR EDIT HARGA PRODUK */
$(document).ready(function () {

   const $modal = $('#modal-edit-harga');
   if ($modal.length === 0) return;

   // ✅ EVENT DELEGATION
   $(document).on('click', '.btn-edit-harga', function () {

      const id = $(this).data('id');
      const produk = $(this).data('produk');
      const harga = $(this).data('harga'); // boleh undefined

      // console.log(id, produk, harga); // 🔥 SEKARANG AKAN MUNCUL

      // if (!id || !produk || !harga) {
      //    console.error('ID atau nama produk tidak ditemukan');
      //    return;
      // }

      // set data ke modal
      $('.produk-info-harga').text(produk);

      if (typeof harga !== 'undefined') {
         $('#edit-harga-jual').val(formatRupiah(harga));
      } else {
         $('#edit-harga-jual').val('');
      }

      // 3️⃣ SET ACTION FORM DARI data-update-template
      const templateUrl = $modal.data('update-template');
      const actionUrl = templateUrl.replace(':id', id);

      $('#formEditHarga').attr('action', actionUrl);

      bootstrap.Modal.getOrCreateInstance($modal[0]).show();
   });

});

/** JS FOR ADD STOK MASUK TOKO (FIX EVENT DELEGATION) */
$(document).ready(function () {

   const $modal = $('#modal-add-produk-toko');
   if ($modal.length === 0) return;

   // ✅ EVENT DELEGATION
   $(document).on('click', '.btn-tambah-produk-toko', function () {

      const id = $(this).data('id');
      const produk = $(this).data('produk');
      const harga = $(this).data('harga'); // boleh undefined
      const hasVarian = Number($(this).data('has-varian')) >= 1;
      console.log(hasVarian);

      // console.log(id, produk, harga); // 🔥 SEKARANG AKAN MUNCUL

      if (!id || !produk) {
         console.error('ID atau nama produk tidak ditemukan');
         return;
      }

      const hargaWrapper = $('.harga-jual-wrapper');
      const hargaInput = $('#harga_jual');
      console.log(hargaWrapper, hargaInput);
      // set data ke modal
      $('#id_produk').val(id);
      $('.produk-info').text(produk);

      if (hasVarian) {
         hargaWrapper.addClass('d-none');
         hargaInput.val('');
         hargaInput.removeAttr('required');
      } else {
         hargaWrapper.removeClass('d-none');
         hargaInput.attr('required', true);
      }

      if (typeof harga !== 'undefined') {
         $('#harga_jual').val(formatRupiah(harga));
      } else {
         $('#harga_jual').val('');
      }

      bootstrap.Modal.getOrCreateInstance($modal[0]).show();
   });

});

/** JS FOR UPDATE STOK MASUK TOKO */
$(document).ready(function () {

   const $modal = $('#modal-edit-stok-toko');
   if ($modal.length === 0) return;

   $(document).on('click', '.btn-edit-stok-toko', function () {

      const stokId = $(this).data('id');        // stok.id
      const produkId = $(this).data('produk-id');
      const produk = $(this).data('produk');
      const stok = $(this).data('stok');

      if (!stokId) {
         console.error('stok_id tidak ditemukan');
         return;
      }

      // 1️⃣ Set info produk
      $modal.find('.produk-info').text(produk);

      // 2️⃣ Set input value
      $('#produk_id_edit').val(produkId);
      $('#stok_lama').val(stok);
      $('#stok_masuk_edit').val(stok);

      // 3️⃣ SET ACTION FORM DARI data-update-template
      const templateUrl = $modal.data('update-template');
      const actionUrl = templateUrl.replace(':id', stokId);

      $('#formEditStokToko').attr('action', actionUrl);

      // 4️⃣ Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });

});


/** JS FOR HAPUS STOK TOKO*/
$(document).ready(function () {

   const $modal = $('#modal-hapus-stok-toko');
   if ($modal.length === 0) return;

   $(document).on('click', '.btn-hapus-stok-toko', function () {

      const stokId = $(this).data('id');
      const produk = $(this).data('produk');
      const stok = $(this).data('stok');
      const tanggal = $(this).data('tanggal');

      if (!stokId) {
         console.error('stok_id tidak ditemukan');
         return;
      }

      // Tampilkan info konfirmasi
      $modal.find('.confirm')
         .text(`Hapus stok "${produk}" sebanyak ${stok}?`);

      // Ambil template URL
      const templateUrl = $modal.data('delete-template');

      // Set ID ke tombol konfirmasi
      const $btnConfirm = $('#btn-confirm-hapus-stok-toko');
      $btnConfirm.data('id', stokId);

      // Hindari double binding
      $btnConfirm.off('click').on('click', function (e) {
         e.preventDefault();

         const url = templateUrl.replace(':id', $(this).data('id'));
         window.location.href = url;
      });

      // Show modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });

});


/** JS FOR UPDATE STOK KELUAR TOKO */
$(document).ready(function () {

   const $modal = $('#modal-edit-stok-keluar');
   if ($modal.length === 0) return;

   $(document).on('click', '.btn-edit-stok-keluar', function () {

      const stokId = $(this).data('id');        // stok.id
      const produk = $(this).data('produk');
      const stok = $(this).data('stok');
      const keterangan = $(this).data('keterangan');

      if (!stokId || !produk || !stok || !keterangan) {
         console.error(stokId, produkId, produk, stok, harga, keterangan);
         return;
      }


      // 1️⃣ Set info produk
      $modal.find('.produk-info').text(produk);

      // 2️⃣ Set input value
      $('#stok_keluar_id').val(stokId);
      $('#stok_keluar').val(stok);
      $('#keterangan').val(keterangan);


      // 3️⃣ SET ACTION FORM DARI data-update-template
      const templateUrl = $modal.data('update-template');
      const actionUrl = templateUrl.replace(':id', stokId);

      $('#formEditStokKeluar').attr('action', actionUrl);

      // 4️⃣ Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   });

});

/** JS FOR HAPUS STOK KELUAR TOKO */
$(document).ready(function () {

   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-stok-keluar');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus di tabel
   $(document).on('click', '.btn-hapus-stok-keluar', function () {

      const id = $(this).data('id');
      const stok = $(this).data('stok');
      const produk = $(this).data('produk');

      if (!id || !stok || !produk) {
         console.error('Data stok atau produk tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      // Isi teks konfirmasi
      $modalDelete.find('.confirm')
         .text('Yakin ingin menghapus histori stok keluar produk "' + produk + '" sebanyak ' + stok + '?');

      // Tombol konfirmasi hapus
      const $btnConfirmDelete = $('#btn-confirm-hapus-stok-keluar');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');

      // Set stokId ke tombol konfirmasi
      $btnConfirmDelete.data('id', id);

      // Hindari double binding
      $btnConfirmDelete.off('click').on('click', function (e) {
         e.preventDefault();

         const id = $(this).data('id');
         const href = templateUrl.replace(':id', id);

         window.location.href = href;
      });
   });

});

/** JS FOR UPDATE SHIFT */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-shift');
   const $form = $('#formEditShift');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $('.btn-edit-shift').on('click', function () {
      const id = $(this).data('id');
      const shift = $(this).data('shift');
      const start_time = $(this).data('start_time');
      const end_time = $(this).data('end_time');

      if (!id || !shift || !start_time || !end_time) {
         console.error('Data tidak ditemukan pada tombol tambah stok.');
         return;
      }

      $('#id-shift-edit').val(id);
      $('#shift_edit').val(shift);
      $('#start_time_edit').val(start_time);
      $('#end_time_edit').val(end_time);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
});

/** JS FOR HAPUS SHIFT */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-shift');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $('.btn-hapus-shift').on('click', function () {
      const id = $(this).data('id');
      const shift = $(this).data('shift');

      if (!id || !shift) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Hapus shift ' + shift + '?');

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus-shift');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR UPDATE KASIR */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-kasir');
   const $form = $('#formEditKasir');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $(document).on('click', '.btn-edit-kasir', function () {
      const id = $(this).data('id');
      const name = $(this).data('name');
      const username = $(this).data('username');
      const email = $(this).data('email');
      const isactive = $(this).data('isactive');

      if (!id || !name || !username || !email || !isactive) {
         console.error('Data tidak ditemukan pada tombol tambah stok.');
         return;
      }

      $('#id-edit').val(id);
      $('#name-edit').val(name);
      $('#username-edit').val(username);
      $('#email-edit').val(email);
      $('#isactive-edit').prop('checked', isactive == 1);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
});

/** JS FOR HAPUS KASIR */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-kasir');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $(document).on('click', '.btn-hapus-kasir', function () {
      const id = $(this).data('id');
      const name = $(this).data('name');

      if (!id || !name) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Nonaktifkan ' + name + '?');

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR HAPUS PENGELUARAN*/
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-pengeluaran');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $(document).on('click', '.btn-hapus-pengeluaran', function () {
      const id = $(this).data('id');
      const deskripsi = $(this).data('deskripsi');
      const jumlah = $(this).data('jumlah');

      if (!id || !deskripsi || !jumlah) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Hapus ' + deskripsi + ' sejumlah Rp.' + formatRupiah(jumlah) + '?');

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR UPDATE PRINTER */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-printer');
   const $form = $('#formEditPrinter');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $('.btn-edit-printer').on('click', function () {
      const id = $(this).data('id');
      const printer = $(this).data('nama');
      const kertas = $(this).data('kertas');


      if (!id || !printer || !kertas) {
         console.error('Data tidak ditemukan pada tombol tambah printer.');
         return;
      }

      $('#printer-edit').val(printer);
      $('#kertas-edit').val(kertas);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
});

/** JS FOR HAPUS PRINTER */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modalDelete = $('#modal-hapus-printer');
   if ($modalDelete.length === 0) return;

   // Event klik tombol hapus
   $('.btn-hapus-printer').on('click', function () {
      const id = $(this).data('id');
      const name = $(this).data('printer');

      if (!id || !name) {
         console.error('Data tidak ditemukan pada tombol hapus.');
         return;
      }

      // Tampilkan modal hapus
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modalDelete[0]);
      modalInstance.show();

      $('.confirm').text('Hapus printer ' + name + '?');

      // Pastikan tombol konfirmasi hapus ada di modal
      const $btnConfirmDelete = $('#btn-confirm-hapus');
      if ($btnConfirmDelete.length === 0) return;

      const templateUrl = $modalDelete.data('delete-template');
      // Set data-id pada tombol konfirmasi hapus
      $btnConfirmDelete.data('id', id);
      // Event klik tombol konfirmasi hapus
      $btnConfirmDelete.on('click', function (e) {
         e.preventDefault();
         const href = templateUrl.replace(':id', $btnConfirmDelete.data('id'));
         window.location.href = href;
      });
   });
});

/** JS FOR UPDATE ADMIN */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-admin');
   const $form = $('#formEditAdmin');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $('.btn-edit-admin').on('click', function () {
      const id = $(this).data('id');
      const name = $(this).data('name');
      const username = $(this).data('username');
      const email = $(this).data('email');
      const isactive = $(this).data('isactive');
      let tenants = $(this).data('tenants'); // ARRAY

      if (!id || !name || !username || !email || !isactive || !tenants) {
         console.error('Data tidak ditemukan pada tombol tambah stok.');
         return;
      }

      $('#id-edit').val(id);
      $('#name-edit').val(name);
      $('#username-edit').val(username);
      $('#email-edit').val(email);
      $('#isactive-edit').prop('checked', isactive == 1);
      let daftarToko = $('#toko-edit');

      // Pastikan array
      if (typeof tenants === 'string') {
         tenants = JSON.parse(tenants);
      }

      tenants = tenants.map(String);

      // RESET SEMUA OPTION
      daftarToko.find('option').prop('selected', false);

      // SET SELECTED YANG SESUAI
      daftarToko.find('option').each(function () {
         if (tenants.includes($(this).val())) {
            $(this).prop('selected', true);
         }
      });

      // 🔥 WAJIB: trigger change agar Choices update UI
      daftarToko.trigger('change');

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
});

/** JS FOR UPDATE CATATAN */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-catatan');
   const $form = $('#formEditCatatan');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $('.btn-edit-catatan').on('click', function () {
      const id = $(this).data('id');
      const catatan = $(this).data('catatan');


      if (!id || !catatan) {
         console.error('Data tidak ditemukan pada tombol edit');
         return;
      }

      $('#id-edit').val(id);
      $('#catatan-edit').val(catatan);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
})

/** JS FOR UPDATE PELANGGAN */
$(document).ready(function () {
   // Pastikan modal ada di halaman
   const $modal = $('#modal-edit-pelanggan');
   const $form = $('#formEditPelanggan');
   if ($modal.length === 0) return;

   // Ambil URL template dari data attribute
   const templateUrl = $modal.data('update-template');

   $('.btn-edit-pelanggan').on('click', function () {
      const id = $(this).data('id');
      const nama = $(this).data('nama');
      const alamat = $(this).data('alamat');
      const whatsapp = $(this).data('whatsapp');


      if (!id || !nama || !whatsapp) {
         console.error('Data tidak ditemukan pada tombol edit');
         return;
      }

      $('#nama-edit').val(nama);
      $('#alamat-edit').val(alamat);
      $('#whatsapp-edit').val(whatsapp);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id);
      $form.attr('action', actionUrl);

      // Tampilkan modal
      const modalInstance = bootstrap.Modal.getOrCreateInstance($modal[0]);
      modalInstance.show();
   })
})

// scan barcode page produk
$(document).ready(function () {
   let scanner = null;
   const scanButton = $("#scan-button");
   const closeButton = $("#closeCamera");
   const modal = $("#modalScanQr");
   const scannerContainer = $("#scanner-container");
   const codeInput = $("#kode"); // Ambil input hasil scan

   scanButton.on("click", function () {
      modal.modal("show");
   });

   modal.on("shown.bs.modal", function () {
      startScanner();
   });

   function startScanner() {
      if (scanner) return; // Hindari scanner duplikat

      scannerContainer.empty(); // Bersihkan scanner jika ada sisa dari pemindaian sebelumnya
      scanner = new Html5QrcodeScanner("scanner-container", {
         fps: 10,
         qrbox: {
            width: 250,
            height: 250
         }
      });

      scanner.render(
         (decodedText) => {
            // console.log("✅ Barcode:", decodedText);
            codeInput.val(decodedText); // Set hasil scan ke input
            alert("Hasil Scan: " + decodedText);
            stopScanner();
            $('#price-input').focus();
         },
         (error) => {
            if (error !==
               "QR code parse error, error = D: No MultiFormat Readers were able to detect the code."
            ) {
               console.warn("Error scanner:", error);
            }
         }
      );
   }

   function stopScanner() {
      if (scanner) {
         scanner.clear().then(() => {
            console.log("⏹ Scanner dihentikan");
            scanner = null;
         }).catch(err => console.error("❌ Gagal menghentikan scanner:", err));
      }
      modal.modal("hide");
   }

   closeButton.on("click", stopScanner);
});

// scan barcode page transaksi
$(document).ready(function () {
   let scanner = null;
   const scanButton = $("#scan-button");
   const closeButton = $("#closeCamera");
   const modal = $("#modalScanQr");
   const scannerContainer = $("#scanner-container");
   const search = $("#search"); // Ambil input hasil scan

   scanButton.on("click", function () {
      modal.modal("show");
   });

   modal.on("shown.bs.modal", function () {
      startScanner();
   });

   function startScanner() {
      if (scanner) return; // Hindari scanner duplikat

      scannerContainer.empty(); // Bersihkan scanner jika ada sisa dari pemindaian sebelumnya
      scanner = new Html5QrcodeScanner("scanner-container", {
         fps: 10,
         qrbox: {
            width: 250,
            height: 250
         }
      });

      scanner.render(
         (decodedText) => {
            // console.log("✅ Barcode:", decodedText);
            search.val(decodedText); // Set hasil scan ke input
            alert("Hasil Scan: " + decodedText);
            stopScanner();
            $('#price-input').focus();
         },
         (error) => {
            if (error !==
               "QR code parse error, error = D: No MultiFormat Readers were able to detect the code."
            ) {
               console.warn("Error scanner:", error);
            }
         }
      );
   }

   function stopScanner() {
      if (scanner) {
         scanner.clear().then(() => {
            console.log("⏹ Scanner dihentikan");
            scanner = null;
         }).catch(err => console.error("❌ Gagal menghentikan scanner:", err));
      }
      modal.modal("hide");
   }

   closeButton.on("click", stopScanner);
});

// input NUMBER
$(document).ready(function () {
   $('.input-number').on('input', function () {
      // Ambil angka saja
      let value = $(this).val().replace(/[^0-9]/g, '');

      // Format ribuan dengan koma
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

      $(this).val(value);
   });

});

// FORMAT NO TELEPON
$(document).ready(function () {
   $('#whatsapp').on('input', function () {
      this.value = this.value.replace(/[^0-9]/g, '');
   });
});

// TUKAR POIN
$(document).ready(function () {
   $('.editPelanggan').click(function (e) {
      e.preventDefault();

      $('#modalPelanggan').modal('show');
      $('#modalPelanggan .modal-title').text('Penukaran Poin Pelanggan');
      $('#methodField').val('PUT');

      let idPelanggan = $(this).data('id_pelanggan');
      let nama = $(this).data('nama');
      let alamat = $(this).data('alamat');
      let telepon = $(this).data('telepon');
      let poin = $(this).data('poin');

      $('#nama').text(nama);
      $('#alamat').text(alamat);
      $('#telepon').text(telepon);
      $('#poin-text').text(poin);

      $('#poin_ditukar').on('input', function () {
         let poin_input = $(this).val();
         if (poin_input <= poin) {
            let sisa_poin = poin - poin_input;
            $('#poin-text').text(sisa_poin);
         } else {
            $('#poin-text').text(poin);
            $('#poin_ditukar').val('');
         }
      })

      $('#formPelanggan').attr('action', '/prosesPenukaranPoin/' + idPelanggan);
   });
});

if (
   typeof window.chartBulanan !== 'undefined' &&
   document.querySelector('#chartPendapatan')
) {

   var options = {
      chart: {
         type: 'line',
         height: 350
      },
      series: [
         {
            name: 'Pendapatan',
            data: window.chartBulanan.pendapatan
         },
         {
            name: 'Pengeluaran',
            data: window.chartBulanan.pengeluaran
         }
      ],
      xaxis: {
         categories: window.chartBulanan.labels
      },
      stroke: {
         curve: 'smooth',
         width: 2
      },
      tooltip: {
         y: {
            formatter: function (val) {
               return 'Rp ' + val.toLocaleString('id-ID');
            }
         }
      }
   };

   new ApexCharts(
      document.querySelector("#chartPendapatan"),
      options
   ).render();
}


function formatRupiahBaru(value) {
   if (!value) return '';

   // ambil angka bulat saja (buang .00)
   let number = Math.floor(parseFloat(value));

   return number.toLocaleString('id-ID');
}

// RETUR DAN PEMBATALAN
$(document).ready(function () {
   $('.btn-batal-dan-retur').on('click', function (e) {
      e.preventDefault();

      const $modal = $('#exampleModal');
      if ($modal.length === 0) return;
      const $form = $('#formUpdateJumlahProduk');

      let id_detail_transaksi = $(this).data('id_detail_transaksi');
      let nama_produk = $(this).data('nama_produk');
      let jumlah = $(this).data('jumlah');
      let harga_satuan = parseFloat($(this).data('harga_satuan'));
      let total_harga_produk = parseFloat($(this).data('total_harga_produk'));

      // Ambil URL template dari data attribute
      const templateUrl = $modal.data('update-template');
      // console.log(templateUrl);

      // console.log(id_detail_transaksi, nama_produk, jumlah, harga_satuan, total_harga_produk);

      $('#exampleModal').modal('show');
      $('#id_detail_transaksi_val').val(id_detail_transaksi);
      $('#nama_produk_text').text(nama_produk);
      $('#jumlah_text').text(jumlah);
      $('.jumlah_val').val(jumlah);
      $('#harga_satuan_text').text(harga_satuan.toLocaleString('id-ID'));
      $('#total_harga_produk_text').text(total_harga_produk.toLocaleString('id-ID'));

      // let url = "{{ route('kasir.updateProdukTransaksi', ':id') }}";
      // url = url.replace(':id', id_detail_transaksi);
      // // console.log('URL ' + url);

      // $('#formUpdateJumlahProduk').attr('action', url);

      // Ganti action form
      const actionUrl = templateUrl.replace(':id', id_detail_transaksi);
      $form.attr('action', actionUrl);
   })

   $(document).on('click', '.btn-reset', function (e) {
      e.preventDefault();
      $('#update_jumlah_text').html('');
      $('.jumlah_val').val($('#jumlah_text').text());
      $('#update_total_harga_produk_text').html('');
      $('#jumlah_retur').val(0);
      $('.btn-kurangi').prop('disabled', false);
   });

   $('.modal-body')
      .off('click', '.btn-kurangi')
      .on('click', '.btn-kurangi', function () {

         let jumlah_sekarang = parseInt($('.jumlah_val').val());
         let harga_satuan = parseInt(
            $('#harga_satuan_text').text().replace(/\./g, '')
         );

         let jumlah_retur = parseInt($('#jumlah_retur').val()) || 0;

         if (jumlah_sekarang <= 0)
            return; jumlah_sekarang--; jumlah_retur++; $('.jumlah_val').val(jumlah_sekarang);
         $('#jumlah_retur').val(jumlah_retur); $('#update_jumlah_text').html('<i class="bi bi-arrow-right px-2"></i> ' +
            jumlah_sekarang); let total_update = harga_satuan * jumlah_sekarang;
         $('#update_total_harga_produk_text').html('<i class="bi bi-arrow-right px-2 mb-3"></i> ' +
            total_update.toLocaleString('id-ID')
         );

         if (jumlah_sekarang == 0) {
            $('.btn-kurangi').prop('disabled', true);
         }
      });

   $('#exampleModal').on('hidden.bs.modal', function () {
      // reset value
      $('.jumlah_val').val('');
      $('#jumlah_retur').val(0);
      $('#type').val('');
      $('#keterangan').val('');

      // reset tampilan
      $('#update_jumlah_text').html('');
      $('#update_total_harga_produk_text').html('');

      // optional (biar bersih total)
      $('#harga_satuan_text').text('');
      $('#total_harga_produk_text').text('');
      $('#nama_produk_text').text('');

      $('.btn-kurangi').prop('disabled', false);
   });

});



