<div class="modal fade text-left modal-borderless" id="modal-hapus-kasir" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-delete-template="{{ url('/toko/kasir/delete/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Nonaktifkan Kasir</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <div class="modal-body">
            <p class="confirm text-danger"></p>
            <div class="modal-footer">
               <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                  <span class="d-sm-block">Batal</span>
               </button>
               <button id="btn-confirm-hapus" class="btn btn-outline-danger rounded-pill ms-1">
                  <span class="d-sm-block">Hapus</span>
               </button>

            </div>
         </div>
      </div>
   </div>
</div>