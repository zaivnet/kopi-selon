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


$(document).ready(function () {
   $(document).on('click', '.btn-edit-toko', function (e) {
      e.preventDefault();
      e.stopPropagation();
   });
});

$(document).ready(function () {
   $(document).on('click', '.btn-hapus-toko', function (e) {
      e.preventDefault();
      e.stopPropagation();
   });
});

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

document.addEventListener('DOMContentLoaded', function () {
   const toggle = document.getElementById('toggle-dark');
   const bodyKasir = document.getElementById('body-kasir');
   const navbarKasir = document.querySelector('.kasir-navbar');

   if (!toggle || !bodyKasir) return;

   toggle.addEventListener('click', () => {
      // ⏱ beri jeda kecil agar localStorage sudah ter-update
      setTimeout(() => {
         const theme = localStorage.getItem('theme');
         if (!theme) return;

         // 1️⃣ reset class theme di body kasir
         bodyKasir.classList.remove('dark', 'light');
         bodyKasir.classList.add(theme);

         // 2️⃣ reset navbar (safety)
         if (navbarKasir) {
            navbarKasir.classList.remove('light', 'dark');
         }
      }, 0);
   });
});
