{{-- modal tambah catatan --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-catatan" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/catatan/update/:id') }}">
   <div class=" modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Catatan</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form id="formEditCatatan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <div class="form-group">
                  <textarea name="catatan" class="form-control" id="catatan-edit" rows="2" autofocus required
                     placeholder="Catatan anda">{{old('catatan')}}</textarea>
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