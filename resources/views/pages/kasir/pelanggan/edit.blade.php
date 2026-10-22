{{-- modal tambah pelanggan --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-pelanggan" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/pelanggan/update/:id') }}">
   <div class=" modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Pelanggan</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form id="formEditPelanggan" method="POST">
            @csrf
            @method('GET')
            <div class="modal-body">
               <label for="nama-edit">Nama Pelanggan</label>
               <div class="form-group">
                  <input type="text" id="nama-edit" class="form-control" name="nama"
                     placeholder="Masukkan nama pelanggan" required autofocus>
               </div>
               <label for="alamat-edit">Alamat</label>
               <div class="form-group">
                  <input type="text" id="alamat-edit" class="form-control" name="alamat"
                     placeholder="Alamat boleh dkosongi" autofocus>
               </div>
               <label for="whatsapp-edit">No. Whatsapp</label>
               <div class="form-group">
                  <input type="text" id="whatsapp-edit" class="form-control" name="whatsapp"
                     placeholder="Nomor whatsapp pelanggan" required autofocus>
               </div>
               <div class="modal-footer px-0">
                  <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                     <span class="d-sm-block">Batal</span>
                  </button>
                  <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                     <span class="d-sm-block">Update</span>
                  </button>
               </div>
            </div>
         </form>

      </div>
   </div>
</div>