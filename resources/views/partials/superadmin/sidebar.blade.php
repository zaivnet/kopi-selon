<div id="sidebar">
   <div class="sidebar-wrapper active">
      <div class="sidebar-header position-relative">
         <div class="d-flex justify-content-between align-items-center">
            <div class="logo">

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

            @if (auth()->user()->name != 'Owner')
            <li class="sidebar-title">Menu</li>

            <li class="sidebar-item {{request()->routeIs('superadmin.dashboard') ? 'active' : ''}}">

               <a href="{{route('superadmin.dashboard')}}" class='sidebar-link'>
                  <i class="bi bi-grid-fill"></i>
                  <span>Dashboard</span>
               </a>
            </li>
            <li class="sidebar-item {{request()->routeIs('superadmin.toko') ? 'active' : ''}}">
               <a href="{{route('superadmin.toko')}}" class='sidebar-link'>
                  <i class="bi bi-house-door-fill"></i>
                  <span>Toko</span>
               </a>
            </li>


            <li class="sidebar-item has-sub {{ Request::is('gudang') || Request::is('gudang/*') ? 'active' : '' }}">
               <a href="" class='sidebar-link'>
                  <i class="bi bi-box-seam-fill"></i>
                  <span>Gudang</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item {{ request()->routeIs('superadmin.gudang') ? 'active' : '' }} ">
                     <a href="{{ route('superadmin.gudang')}}" class="submenu-link">Kelola Gudang</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('superadmin.gudang.barang-masuk') ? 'active' : '' }}">
                     <a href="{{route('superadmin.gudang.barang-masuk')}}" class="submenu-link">Barang Masuk</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('superadmin.gudang.barang-keluar') ? 'active' : '' }}">
                     <a href="{{route('superadmin.gudang.barang-keluar')}}" class="submenu-link">Barang Keluar</a>
                  </li>
               </ul>

            </li>
            @endif

            <li
               class="sidebar-item has-sub {{request()->routeIs('superadmin.pendapatan') || request()->routeIs('superadmin.pengeluaran') ? 'active' : ''}}">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cash-coin"></i>
                  <span>Keuangan</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item {{ request()->routeIs('superadmin.pendapatan') ? 'active' : '' }} ">
                     <a href="{{route('superadmin.pendapatan')}}" class="submenu-link">Pendapatan</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('superadmin.pengeluaran') ? 'active' : '' }} ">
                     <a href="{{route('superadmin.pengeluaran')}}" class="submenu-link">Pengeluaran</a>
                  </li>
               </ul>

            </li>

            <li
               class="sidebar-item has-sub {{request()->routeIs('superadmin.log.gudang') || request()->routeIs('superadmin.log.toko') ? 'active' : ''}}">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cash-coin"></i>
                  <span>Logs</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item {{ request()->routeIs('superadmin.log.gudang') ? 'active' : '' }} ">
                     <a href="{{route('superadmin.log.gudang')}}" class="submenu-link">Log Gudang</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('superadmin.log.toko') ? 'active' : '' }} ">
                     <a href="{{route('superadmin.log.toko')}}" class="submenu-link">Log Toko</a>
                  </li>
               </ul>

            </li>

            @if (auth()->user()->name != 'Owner')
            <li class="sidebar-title">Pengaturan</li>

            <li class="sidebar-item {{request()->routeIs('superadmin.admin') ? 'active' : ''}}">
               <a href="{{route('superadmin.admin')}}" class='sidebar-link'>
                  <i class=" bi bi-people-fill"></i>
                  <span>Admin</span>
               </a>
            </li>
            @endif
         </ul>
      </div>
   </div>
</div>