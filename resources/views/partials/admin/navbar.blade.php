<header>
   <nav class="navbar navbar-expand navbar-light navbar-top">
      <div class="container-fluid">
         <a href="#" class="burger-btn d-block">
            <i class="bi bi-justify fs-3"></i>
         </a>

         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>


         <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-lg-0 d-flex align-items-center gap-3">

               <li class="nav-item dropdown">
                  <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                     style="left: 20%; transform: translateX(-72%); top: 100%;" aria-labelledby="dropdownMenuButton">
                     <li class="dropdown-header">
                        <h6>Notifications</h6>
                     </li>
                     <li class="dropdown-item notification-item">
                        <a class="d-flex align-items-center" href="#">
                           <div class="notification-icon bg-primary">
                              <i class="bi bi-cart-check"></i>
                           </div>
                           <div class="notification-text ms-4">
                              <p class="notification-title font-bold">Successfully check out</p>
                              <p class="notification-subtitle font-thin text-sm">Order ID #256
                              </p>
                           </div>
                        </a>
                     </li>
                     <li class="dropdown-item notification-item">
                        <a class="d-flex align-items-center" href="#">
                           <div class="notification-icon bg-success">
                              <i class="bi bi-file-earmark-check"></i>
                           </div>
                           <div class="notification-text ms-4">
                              <p class="notification-title font-bold">Homework submitted</p>
                              <p class="notification-subtitle font-thin text-sm">Algebra math
                                 homework</p>
                           </div>
                        </a>
                     </li>
                     <li>
                        <p class="text-center py-2 mb-0"><a href="#">See all
                              notification</a></p>
                     </li>
                  </ul>
               </li>

               <div class="dropdown">
                  <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                     <div class="user-menu d-flex">
                        <div class="user-img d-flex align-items-center">
                           <div class="avatar avatar-md">
                              <img src="{{ asset('assets/compiled/jpg/2.jpg') }}" alt="Avatar" class="rounded-circle">
                           </div>
                        </div>
                     </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton"
                     style="min-width: 11rem; top: calc(100% + 0.4rem);">
                     <li>
                        <h6 class="dropdown-header">Halo, {{ auth()->user()->name }}</h6>
                     </li>
                     <li><a class="dropdown-item" href="{{route('profile.edit', auth()->user()->id)}}"><i
                              class="icon-mid bi bi-person me-2"></i>Profil</a>
                     </li>

                     <hr class="dropdown-divider">
                     </li>
                     <li><a class="dropdown-item" href="{{ route('logout') }}"><i
                              class="icon-mid bi bi-box-arrow-left me-2"></i>
                           Logout</a></li>
                  </ul>
               </div>
            </ul>

         </div>
      </div>
   </nav>
</header>