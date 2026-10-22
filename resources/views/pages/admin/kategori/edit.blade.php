{{-- modal edit jabatan --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-kategori" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true"
   data-update-template="{{ url('/toko/gudang/kategori/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Jabatan</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditKategori" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <input type="hidden" name="id-kategori" id="id-kategori">
               <label for="kategori">Nama Kategori</label>
               <div class="form-group">
                  <input type="text" id="kategori" class="form-control" name="kategori"
                     placeholder="Masukkan nama kategori" required autofocus>
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