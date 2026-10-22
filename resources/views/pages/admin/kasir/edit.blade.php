{{-- modal edit kasir --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-kasir" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/kasir/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Kasir</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditKasir" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <input type="hidden" name="id-edit" id="id-edit">
               <label for="name-edit">Nama Kasir</label>
               <div class="form-group">
                  <input type="text" id="name-edit" class="form-control" name="name" placeholder="Masukkan nama kasir"
                     required autofocus>
               </div>
               <label for="username-edit">Username</label>
               <div class="form-group">
                  <input type="text" id="username-edit" class="form-control" name="username"
                     placeholder="Masukkan waktu mulai" required autofocus>
               </div>
               <label for="email-edit">Email</label>
               <div class="form-group">
                  <input type="email" id="email-edit" class="form-control" name="email" placeholder="Masukkan email"
                     required autofocus>
               </div>
               <label for="password-edit">Password</label>
               <div class="form-group">
                  <input type="text" id="password-edit" class="form-control" name="password"
                     placeholder="Masukkan password baru" autofocus>
               </div>
               <div class="form-check form-switch mt-3">
                  <input class="form-check-input" name="isActive" type="checkbox" id="isactive-edit" checked="">
                  <label class="form-check-label" for="isactive-edit">Aktif/Nonaktif</label>
               </div>

               <div class="modal-footer px-0">
                  <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                     <span class="d-sm-block">Batal</span>
                  </button>
                  <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                     <span class="d-sm-block">Simpan</span>
                  </button>

               </div>
            </div>
         </form>

      </div>
   </div>
</div>