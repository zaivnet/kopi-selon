<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>{{$title}}</title>

   <link rel="shortcut icon" href="{{asset('/assets/compiled/svg/favicon.svg')}}" type="image/x-icon">
   <link rel="shortcut icon"
      href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACEAAAAiCAYAAADRcLDBAAAEs2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjMzIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzQiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSIzMyIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzQiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249Ijk2LjAiCiAgIHRpZmY6WVJlc29sdXRpb249Ijk2LjAiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJzUkdCIElFQzYxOTY2LTIuMSIKICAgeG1wOk1vZGlmeURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiCiAgIHhtcDpNZXRhZGF0YURhdGU9IjIwMjItMDMtMzFUMTA6NTA6MjMrMDI6MDAiPgogICA8eG1wTU06SGlzdG9yeT4KICAgIDxyZGY6U2VxPgogICAgIDxyZGY6bGkKICAgICAgc3RFdnQ6YWN0aW9uPSJwcm9kdWNlZCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWZmaW5pdHkgRGVzaWduZXIgMS4xMC4xIgogICAgICBzdEV2dDp3aGVuPSIyMDIyLTAzLTMxVDEwOjUwOjIzKzAyOjAwIi8+CiAgICA8L3JkZjpTZXE+CiAgIDwveG1wTU06SGlzdG9yeT4KICA8L3JkZjpEZXNjcmlwdGlvbj4KIDwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InIiPz5V57uAAAABgmlDQ1BzUkdCIElFQzYxOTY2LTIuMQAAKJF1kc8rRFEUxz9maORHo1hYKC9hISNGTWwsRn4VFmOUX5uZZ36oeTOv954kW2WrKLHxa8FfwFZZK0WkZClrYoOe87ypmWTO7dzzud97z+nec8ETzaiaWd4NWtYyIiNhZWZ2TvE946WZSjqoj6mmPjE1HKWkfdxR5sSbgFOr9Ll/rXoxYapQVik8oOqGJTwqPL5i6Q5vCzeo6dii8KlwpyEXFL519LjLLw6nXP5y2IhGBsFTJ6ykijhexGra0ITl5bRqmWU1fx/nJTWJ7PSUxBbxJkwijBBGYYwhBgnRQ7/MIQIE6ZIVJfK7f/MnyUmuKrPOKgZLpEhj0SnqslRPSEyKnpCRYdXp/9++msneoFu9JgwVT7b91ga+LfjetO3PQ9v+PgLvI1xkC/m5A+h7F32zoLXug38dzi4LWnwHzjeg8UGPGbFfySvuSSbh9QRqZ6H+Gqrm3Z7l9zm+h+iafNUV7O5Bu5z3L/wAdthn7QIme0YAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAJTSURBVFiF7Zi9axRBGIefEw2IdxFBRQsLWUTBaywSK4ubdSGVIY1Y6HZql8ZKCGIqwX/AYLmCgVQKfiDn7jZeEQMWfsSAHAiKqPiB5mIgELWYOW5vzc3O7niHhT/YZvY37/swM/vOzJbIqVq9uQ04CYwCI8AhYAlYAB4Dc7HnrOSJWcoJcBS4ARzQ2F4BZ2LPmTeNuykHwEWgkQGAet9QfiMZjUSt3hwD7psGTWgs9pwH1hC1enMYeA7sKwDxBqjGnvNdZzKZjqmCAKh+U1kmEwi3IEBbIsugnY5avTkEtIAtFhBrQCX2nLVehqyRqFoCAAwBh3WGLAhbgCRIYYinwLolwLqKUwwi9pxV4KUlxKKKUwxC6ZElRCPLYAJxGfhSEOCz6m8HEXvOB2CyIMSk6m8HoXQTmMkJcA2YNTHm3congOvATo3tE3A29pxbpnFzQSiQPcB55IFmFNgFfEQeahaAGZMpsIJIAZWAHcDX2HN+2cT6r39GxmvC9aPNwH5gO1BOPFuBVWAZue0vA9+A12EgjPadnhCuH1WAE8ivYAQ4ohKaagV4gvxi5oG7YSA2vApsCOH60WngKrA3R9IsvQUuhIGY00K4flQG7gHH/mLytB4C42EgfrQb0mV7us8AAMeBS8mGNMR4nwHamtBB7B4QRNdaS0M8GxDEog7iyoAguvJ0QYSBuAOcAt71Kfl7wA8DcTvZ2KtOlJEr+ByyQtqqhTyHTIeB+ONeqi3brh+VgIN0fohUgWGggizZFTplu12yW8iy/YLOGWMpDMTPXnl+Az9vj2HERYqPAAAAAElFTkSuQmCC"
      type="image/png">
   <link rel="stylesheet" href="{{asset('/assets/compiled/css/app.css')}}">
   <link rel="stylesheet" href="{{asset('/assets/compiled/css/app-dark.css')}}">
   <link rel="stylesheet" href="{{asset('assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
   <link rel="stylesheet" href="{{asset('/assets/compiled/css/custom.css')}}">

   <!-- Toastr CSS -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

   <!-- Toastr JS -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</head>
<style>
   .blink-retur {
      color: #dc3545;
      /* merah */
      font-weight: bold;
      animation: blink 1s infinite;
   }

   @keyframes blink {
      0% {
         opacity: 1;
      }

      50% {
         opacity: 0;
      }

      100% {
         opacity: 1;
      }
   }

   #modalPreviewStruk .modal-body {
      max-height: 400px;
      /* sesuaikan */
      overflow-y: auto;
   }
</style>

<body id="body-kasir">
   <script src="{{asset('/assets/static/js/initTheme.js')}}"></script>
   <div id="app">

      <div id="main" class='layout-horizontal'>
         <div id="main" class="layout-horizontal">
            <header class="mb-3">
               <div class="header-top">
                  <div class="container">
                     <div class="logo d-flex gap-1 align-items-center pt-3">
                        @if ($tenant->logo)
                        <img style="margin-bottom: 10px; width: 30px; height: 100%;"
                           src="{{asset('/assets/logo/'.$tenant->logo)}}" alt="{{$tenant->name}}" srcset="">
                        @endif
                        <h4>{{$tenant->name}}</h4>
                     </div>
                     <div class="header-top-right">
                        <div class="theme-toggle d-flex gap-3  align-items-center mt-2">
                           <div id="theme-icon-wrapper" class="d-flex align-items-center justify-content-center">
                              {{-- icon theme appear here --}}
                           </div>
                           <div class="form-check form-switch fs-6">
                              <input class="form-check-input me-0" type="checkbox" id="toggle-dark"
                                 style="cursor: pointer">
                              <label class="form-check-label"></label>
                           </div>
                        </div>
                        <div class="dropdown">
                           <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                              <div class="avatar avatar-md2 ">
                                 <img src="{{asset('./assets/compiled/jpg/1.jpg')}}" alt="Avatar">
                              </div>

                           </a>
                           <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="topbarUserDropdown"
                              style="min-width: 11rem; top: calc(100% + 0.4rem);">
                              <li>
                                 <h6 class="dropdown-header">Halo, {{ auth()->user()->name }}</h6>
                              </li>
                              <li><a class="dropdown-item"
                                    href="{{route('profile.edit', auth()->user()->id)}}">Profil</a></li>
                              <li>
                                 <hr class="dropdown-divider">
                              </li>
                              <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                           </ul>
                        </div>

                        <!-- Burger button responsive -->
                        <a href="#" class="burger-btn d-block d-xl-none">
                           <i class="bi bi-justify fs-3"></i>
                        </a>
                     </div>
                  </div>
               </div>
               @include('partials.kasir.navbar')

            </header>

            <div class="modal fade" id="modalPreviewStruk" tabindex="-1">
               <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h6 class="modal-title">Preview Rekap</h6>
                     </div>
                     <div class="modal-body" id="preview-struk">
                        <div class="text-center text-muted">Memuat...</div>
                     </div>
                     <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button id="btn-cetak" data-shift-id="" class="btn btn-primary d-block d-md-none">Cetak</button>

                        <button id="btn-cetak-pc" onclick="printStruk()"
                           class="btn btn-primary d-none d-md-block">Cetak</button>
                     </div>
                  </div>
               </div>
            </div>

            <iframe id="print-frame" style="display:none;"></iframe>

            <div class="content-wrapper">
               <div class="container">

                  <div class="page-content">
                     @yield('content')
                     <footer>
                        <div class="footer clearfix mb-0 text-muted">
                           <div class="float-start">
                              <p>{{ date('Y') }} &copy; Mazer</p>
                           </div>
                           <div class="float-end">
                              <p><span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                                 Selon Group</p>
                           </div>
                        </div>
                     </footer>
                  </div>
               </div>
               <!-- <div class="page-heading">
               </div> -->

            </div>
         </div>


      </div>
   </div>


   <script src="{{asset('assets/static/js/components/dark.js')}}"></script>
   <script src="{{asset('assets/static/js/pages/horizontal-layout.js')}}"></script>
   <script src="{{asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
   <script src="{{asset('assets/extensions/jquery/jquery.min.js')}}"></script>
   <script src="{{asset('assets/extensions/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ asset('assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
   <script src="{{asset('assets/static/js/pages/datatables.js') }}"></script>
   <script src="{{asset('assets/compiled/js/app.js')}}"></script>
   <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
   <script src="{{asset('assets/compiled/js/custom.js')}}"></script>
   <script src="{{asset('js/script.js')}}"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

   @if (session('success'))
   <script>
      toastr.success("{{ session('success') }}");
   </script>
   @endif

   @if (session('error'))
   <script>
      toastr.error("{{ session('error') }}");
   </script>
   @endif

   @if ($errors->any())
   <script>
      @foreach ($errors->all() as $error)
         toastr.error(@json($error));
      @endforeach
   </script>
   @endif
   {{-- <script>
      window.toastrSuccess = {
         !!json_encode(session('success')) !!
      };
      window.toastrError = {
         !!json_encode(session('error')) !!
      };
      window.validationErrors = {
         !!json_encode($errors - > all()) !!
      };
   </script> --}}

   <script>
      function previewRekap(shiftId) {
         const modal = new bootstrap.Modal(
            document.getElementById('modalPreviewStruk')
         );

         document.getElementById('preview-struk').innerHTML = 'Memuat...';

         fetch(`/cetak-rekap/${shiftId}`)
            .then(res => res.text())
            .then(html => {
               document.getElementById('preview-struk').innerHTML = html;
               modal.show();
            });

            // SIMPAN shiftId ke tombol cetak
            const btnCetak = document.getElementById('btn-cetak');
            btnCetak.dataset.shiftId = shiftId;
      }

       function printStruk() {
          const iframe = document.getElementById('print-frame');
          const doc = iframe.contentWindow.document;
         
          doc.open();
          doc.write(`
          <html>
         
          <head>
             <title>Cetak</title>
         
             <style>
             @page {
                   size: 57mm auto;
                   margin: 0;
                   font-size: 14px;
                }

                @media print {
                   body {
                      margin: 0;
                   }
                }
               
                body {
                   width: 43mm;
                   font-family: sans-serif;
                   font-size: 14px;
                   margin: 0;
                   padding: 0;
                }
         
                table {
                   width: 100%;
                   font-size: 14px;
                   border-collapse: collapse;
                }
         
                td {
                   font-size: 12px;
                   padding: 2px 0;
                }
         
                .center {
                   text-align: center;
                }
         
                .right {
                   text-align: right;
                }
         
                .bold {
                   font-weight: bold;
                }
         
                .header {
                   font-size: 14px;
                }
         
                .fs-sm {
                   font-size: 8px;
                }

                .fs-lg {
                   font-size: 14px;
                }
             </style>
          </head>
         
          <body>
             ${document.getElementById('print-area').innerHTML}
          </body>
         
          </html>
          `);
          doc.close();
         
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
       }

      document.getElementById('btn-cetak').addEventListener('click', async function () {
         try {
            const shiftId = this.dataset.shiftId;
         
            const res = await fetch(`/cetak-rekap-rawbt/${shiftId}`);
            if (!res.ok) throw new Error('Response error');
         
            const base64 = await res.text();
         
            const encoded = encodeURIComponent(base64);
         
            setTimeout(() => {
               window.location.href = `rawbt://base64,${encoded}`;
            }, 100);
         
         } catch (err) {
            console.error(err);
            alert('Gagal cetak');
         }
      });

      
   </script>

   @yield('scripts')

</body>

</html>