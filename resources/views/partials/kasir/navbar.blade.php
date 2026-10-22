<nav class="main-navbar kasir-navbar">
   <div class="container">

      <ul>

         <li class="menu-item {{ request()->routeIs('kasir.dashboard')
               ? 'active' : '' }}">
            <a href="{{ route('kasir.dashboard') }}" class="menu-link {{ request()->routeIs('kasir.dashboard')
               ? 'active' : '' }}">
               <i class="bi bi-grid-fill p-0 my-0" style="margin-bottom: 7px !important"></i><span>
                  Dashboard</span>
            </a>
         </li>

         <li class="menu-item active has-sub">
            <a href="#"
               class="menu-link {{request()->routeIs('kasir.transaksi') || request()->routeIs('kasir.historiTransaksi') || request()->routeIs('kasir.returProduk') ? 'active' : ''}}">
               <i class="bi bi-cart mb-2"></i><span>Transaksi</span>
            </a>
            <div class="submenu ">
               <!-- Wrap to submenu-group-wrapper if you want 3-level submenu. Otherwise remove it. -->
               <div class="submenu-group-wrapper">
                  <ul class="submenu-group">
                     <li class="submenu-item {{ request()->routeIs('kasir.transaksi')
                                 ? 'active' : '' }}">
                        <a href="{{ route('kasir.transaksi') }}" class="submenu-link ">
                           <span>Transaksi</span>
                        </a>
                     </li>
                     <li class="submenu-item {{ request()->routeIs('kasir.historiTransaksi')
                                                      ? 'active' : '' }}">
                        <a href="{{ route('kasir.historiTransaksi') }}" class="submenu-link">
                           <span>Histori Transaksi</span>
                        </a>
                     </li>
                     <li class="submenu-item {{ request()->routeIs('kasir.returProduk')
                                             ? 'active' : '' }}">
                        <a href="{{ route('kasir.returProduk') }}" class="submenu-link">
                           <span>Pembatalan & Retur</span>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
         </li>

         <li class="menu-item">
            <a href="{{ route('kasir.penukaranPoinPelanggan') }}" class="menu-link {{ request()->routeIs('kasir.penukaranPoinPelanggan')
               ? 'active' : '' }}">
               <i class="bi bi-person-badge p-0 m-0" style="margin-bottom: 7px !important;"></i><span>Pelanggan</span>
            </a>
         </li>

         <li class="menu-item">
            <a href="{{ route('kasir.pengeluaranToko') }}" class="menu-link {{ request()->routeIs('kasir.pengeluaranToko')
                        ? 'active' : '' }}">
               <i class="bi bi-cash-coin p-0 m-0" style="margin-bottom: 7px !important;"></i><span>Pengeluaran
                  Toko</span>
            </a>
         </li>

         <li class="menu-item">
            <a href="{{ route('kasir.historiStokMasuk') }}" class="menu-link {{ request()->routeIs('kasir.historiStokMasuk')
                                 ? 'active' : '' }}">
               <i class="bi bi-box p-0 m-0" style="margin-bottom: 7px !important;"></i><span>Stok
                  Masuk</span>
            </a>
         </li>

         <li class=" menu-item">
            <a href="{{ route('kasir.catatan') }}" class="menu-link {{ request()->routeIs('kasir.catatan')
               ? 'active' : '' }}">
               <i class="bi bi-chat-left-text-fill p-0 my-0" style="margin-bottom: 5px !important"></i><span>
                  Catatan</span>
            </a>
         </li>

         <div class="btn-group mb-1">
            <div class="dropdown dropdown-color-icon">
               <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButtonEmoji"
                  data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="me-50"><i class="bi bi-printer"></i></span> Rekap Data
               </button>
               <div class="dropdown-menu" aria-labelledby="dropdownMenuButtonEmoji" style="">
                  @foreach ($getShifts as $shift)
                  <button class="dropdown-item cetak-rekap {{ $shift['disabled'] ? 'disabled' : '' }}"
                     data-shift-id="{{ $shift['shift_id'] }}" data-tanggal="{{ $shift['tanggal_shift'] }}"
                     onclick="previewRekap({{$shift['shift_id']}})">
                     Shift
                     {{ $shift['label'] }}</button>
                  @endforeach
               </div>
            </div>
         </div>

         {{-- <li class=" menu-item">
            <a href="{{ route('kasir.catatan') }}" class="btn btn-outline-success rounded-pill"><i
                  class="bi bi-printer"></i> Data Rekap</a>
         </li> --}}
      </ul>
   </div>
</nav>