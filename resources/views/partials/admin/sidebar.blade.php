<div id="sidebar">
   <div class="sidebar-wrapper active">
      <div class="sidebar-header position-relative">
         <div class="d-flex justify-content-between align-items-center">
            <div class="logo d-flex">

               <h6 class="text-primary fw-bold mt-3">{{ $tenant->name }}</h6>

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
               <a href="{{route('admin.index')}}" class='sidebar-link'>
                  <i class="bi bi-house-door-fill"></i>
                  <span>Pilih Toko</span>
               </a>
            </li>

            <li class="sidebar-item {{request()->routeIs('admin.dashboard') ? 'active' : ''}}">
               <a href="{{route('admin.dashboard')}}" class="sidebar-link">
                  <i class="bi bi-grid-fill"></i>
                  <span>Dashboard</span>
               </a>
            </li>

            <li class="sidebar-item has-sub {{ Request::is('toko/gudang/**') ? 'active' : '' }}">
               <a href="#" class="sidebar-link">
                  <i class="bi bi-box-seam-fill"></i>
                  <span>Gudang</span>
               </a>

               <ul class="submenu ">
                  <li class="submenu-item {{ request()->routeIs('admin.kategori') ? 'active' : '' }}">
                     <a href="{{route('admin.kategori')}}" class="submenu-link">Kategori</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('admin.gudang') ? 'active' : '' }}">
                     <a href="{{route('admin.gudang')}}" class="submenu-link">Produk
                        Gudang</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('admin.gudang.stok-masuk') ? 'active' : '' }}">
                     <a href="{{route('admin.gudang.stok-masuk')}}" class="submenu-link">Stok
                        Masuk</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('admin.gudang.stok-keluar') ? 'active' : '' }}">
                     <a href="{{route('admin.gudang.stok-keluar')}}" class="submenu-link">Stok
                        Keluar</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('admin.gudang.log') ? 'active' : '' }}">
                     <a href="{{route('admin.gudang.log')}}" class="submenu-link">Log
                        Gudang</a>
                  </li>
               </ul>

            </li>

            <li
               class="sidebar-item has-sub {{ Request::is('toko/produk') || Request::is('toko/produk/**') ? 'active' : '' }}">
               <a href="#" class="sidebar-link">
                  <i class="bi bi-shop"></i>
                  <span>Toko</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item {{ request()->routeIs('admin.produk') ? 'active' : '' }} ">
                     <a href="{{route('admin.produk')}}" class="submenu-link">Produk Toko</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('admin.stok') ? 'active' : '' }}">
                     <a href="{{route('admin.stok')}}" class="submenu-link">Stok Masuk</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('admin.stok-keluar') ? 'active' : '' }}">
                     <a href="{{route('admin.stok-keluar')}}" class="submenu-link">Stok
                        Keluar</a>
                  </li>

                  <li class="submenu-item {{ request()->routeIs('admin.produk-toko.log') ? 'active' : '' }}">
                     <a href="{{route('admin.produk-toko.log')}}" class="submenu-link">Log
                        Toko</a>
                  </li>
               </ul>

            </li>

            <li
               class="sidebar-item  has-sub {{ request()->routeIs('admin.pengeluaran') || request()->routeIs('admin.pendapatan') ? 'active' : '' }}">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cash-coin"></i>
                  <span>Keuangan</span>
               </a>

               <ul class="submenu ">

                  <li class="submenu-item {{ request()->routeIs('admin.pendapatan') ? 'active' : '' }} ">
                     <a href="{{ route('admin.pendapatan')}}" class="submenu-link">Pendapatan</a>
                  </li>
                  <li class="submenu-item {{ request()->routeIs('admin.pengeluaran') ? 'active' : '' }} ">
                     <a href="{{ route('admin.pengeluaran')}}" class="submenu-link">Pengeluaran</a>
                  </li>
               </ul>
            </li>
            @php
            $validasi_retur = App\Models\Validasi::join('transaksis', 'transaksis.id', '=',
            'validasi.transaksi_id')
            ->select('transaksis.id')
            ->where('transaksis.tenant_id', session('active_tenant_id'))->where('validasi.status_validasi',
            'pending')
            ->get();
            $count_validasi_retur = count($validasi_retur);
            @endphp

            <li
               class="sidebar-item  has-sub {{ request()->routeIs('admin.data-transaksi') || request()->routeIs('admin.pembatalanRetur') ? 'active' : '' }}">
               <a href="#" class='sidebar-link'>
                  <i class="bi bi-cash-coin"></i>
                  <span>Transaksi
                     @if($count_validasi_retur > 0)
                     <span class="badge bg-danger ms-1">{{ $count_validasi_retur }}</span>
                     @endif</span>
               </a>

               <ul class="submenu ">
                  <li class="submenu-item {{ request()->routeIs('admin.data-transaksi') ? 'active' : '' }}">
                     <a href="{{route('admin.data-transaksi')}}" class='submenu-link'>
                        <span>Data Transaksi
                           @if($count_validasi_retur > 0)
                           <span class="badge bg-danger ms-1">{{ $count_validasi_retur }}</span>
                           @endif
                        </span>
                     </a>

                  </li>
                  <li class="submenu-item {{ request()->routeIs('admin.pembatalanRetur') ? 'active' : '' }}">
                     <a href="{{route('admin.pembatalanRetur')}}" class='submenu-link'>
                        <span>Pembatalan & Retur
                        </span>
                     </a>

                  </li>
               </ul>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.catatan') ? 'active' : '' }}">
               <a href="{{route('admin.catatan')}}" class='sidebar-link'>
                  <i class="bi bi-chat-left-text-fill"></i>
                  <span>Catatan</span>
               </a>

            </li>

            <li class="sidebar-title">Pengaturan</li>

            <li class="sidebar-item {{ request()->routeIs('admin.kasir') ? 'active' : '' }}">
               <a href="{{route('admin.kasir')}}" class='sidebar-link'>
                  <i class="bi bi-people-fill"></i>
                  <span>Kasir</span>
               </a>

            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.shift') ? 'active' : '' }}">
               <a href="{{route('admin.shift')}}" class='sidebar-link'>
                  <i class="bi bi-clock-fill"></i>
                  <span>Shift</span>
               </a>

            </li>

            {{-- <li class="sidebar-item {{ request()->routeIs('admin.printer') ? 'active' : '' }}">
               <a href="{{ route('admin.printer')}}" class='sidebar-link'>
                  <i class=" bi bi-printer-fill"></i>
                  <span>Printer</span>
               </a>

            </li> --}}

         </ul>
      </div>
   </div>
</div>