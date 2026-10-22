{{-- modal tambah relawan --}}
<div class="modal fade text-left modal-borderless" id="modal-add-kasir" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Kasir</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{ route('admin.kasir.store', $tenant->id) }}" method="POST">
            @csrf
            <div class="modal-body">
               <label for="name">Nama Kasir</label>
               <div class="form-group">
                  <input type="text" id="name" class="form-control" name="name" placeholder="Masukkan nama kasir"
                     required autofocus>
               </div>
               <label for="username">Username</label>
               <div class="form-group">
                  <input type="text" id="username" class="form-control" name="username"
                     placeholder="Masukkan waktu mulai" required autofocus>
               </div>
               <label for="email">Email</label>
               <div class="form-group">
                  <input type="email" id="email" class="form-control" name="email" placeholder="Masukkan email" required
                     autofocus>
               </div>
               <label for="password">Password</label>
               <div class="form-group">
                  <input type="text" id="password" class="form-control" name="password" placeholder="Masukkan password"
                     required autofocus>
               </div>

               <div class="modal-footer px-0">
                  <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                     <span class="d-sm-block">Batal</span>
                  </button>
                  <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                     <span class="d-sm-block">Simpan</span>
                  </button>

               </div>
         </form>

      </div>
   </div>
</div>