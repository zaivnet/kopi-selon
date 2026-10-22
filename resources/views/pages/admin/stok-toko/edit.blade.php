{{-- modal tambah stok--}}
<div class="modal fade text-left modal-borderless" id="modal-edit-stok-toko" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/produk/stok/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Stok</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form id="formEditStokToko" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <small class="produk-info badge bg-info d-block mb-2"></small>

               <input type="hidden" name="produk_id" id="produk_id_edit">
               <input type="hidden" name="stok_lama" id="stok_lama">

               <label for="stok_masuk_edit">Stok Masuk</label>
               <input type="number" name="stok_masuk" id="stok_masuk_edit" class="form-control" min="1" required>

               <div class="modal-footer px-0">
                  <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                     <span class="d-sm-block">Batal</span>
                  </button>
                  <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                     <span class="d-sm-block">Update</span>
                  </button>

               </div>
         </form>

      </div>
   </div>
</div>