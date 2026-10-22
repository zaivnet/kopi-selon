<div id="sidebar">
   <div class="sidebar-wrapper active">
      <div class="sidebar-header position-relative">
         <div class="d-flex justify-content-between align-items-center">
            <div class="logo">
               <a href="index.html"><img src="{{ asset('assets/compiled/svg/logo.svg')}}" alt="Logo" srcset=""></a>
            </div>
            <div class="theme-toggle d-flex gap-3  align-items-center mt-2">
               <div id="theme-icon-wrapper" class="d-flex align-items-center justify-content-center">
                  {{-- icon theme appear here --}}
               </div>
               <div class="form-check form-switch fs-6">
                  <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                  <label class="form-check-label"></label>
               </div>
            </div>
            <div class="sidebar-toggler  x">
               <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
            </div>
         </div>

      </div>
      <div class="sidebar-menu">
         <ul class="menu">

            <li class="sidebar-title">Menu</li>

            <li class="sidebar-item">
               <a href="index.html" class='sidebar-link'>
                  <i class="bi bi-grid-fill"></i>
                  <span>Dashboard</span>
               </a>

            </li>

            <!-- <li class="sidebar-item  has-sub">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-box-seam-fill"></i>
                  <span>Produk</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item  ">
                     <a href="component-accordion.html" class="submenu-link">Data Produk</a>
                  </li>

                  <li class="submenu-item  ">
                     <a href="component-badge.html" class="submenu-link">Kategori</a>
                  </li>

                  <li class="submenu-item  ">
                     <a href="component-alert.html" class="submenu-link">Stok</a>
                  </li>
               </ul>

            </li> -->

            <li class="sidebar-item  has-sub">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cart-fill"></i>
                  <span>Transaksi</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item  ">
                     <a href="{{ route('kasir.transaksi') }}" class="submenu-link">Transaksi</a>
                  </li>

                  <li class="submenu-item  ">
                     <a href="component-badge.html" class="submenu-link">Riwayat Transaksi</a>
                  </li>
               </ul>

            </li>

            <!-- <li class="sidebar-item  has-sub">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cash-coin"></i>
                  <span>Keuangan</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item  ">
                     <a href="component-accordion.html" class="submenu-link">Pendapatan</a>
                  </li>

                  <li class="submenu-item  ">
                     <a href="component-badge.html" class="submenu-link">Pengeluaran</a>
                  </li>
               </ul>

            </li> -->


            <!-- <li class="sidebar-title">Pengaturan</li>

            <li class="sidebar-item">
               <a href="index.html" class='sidebar-link'>
                  <i class="bi bi-shop"></i>
                  <span>Toko</span>
               </a>

            </li> -->

            <!-- <li class="sidebar-item">
               <a href="index.html" class='sidebar-link'>
                  <i class="bi bi-people-fill"></i>
                  <span>Pengguna</span>
               </a>

            </li>

            <li class="sidebar-item">
               <a href="index.html" class='sidebar-link'>
                  <i class="bi bi-clock-fill"></i>
                  <span>Shift</span>
               </a>

            </li> -->

         </ul>
      </div>
   </div>
</div>