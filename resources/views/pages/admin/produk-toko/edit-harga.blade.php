<div class="modal fade text-left modal-borderless" id="modal-edit-harga" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/produk/update-harga/:id') }}">

   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">

         <div class="modal-header">
            <h5 class="modal-title">Edit Produk</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditHarga" method="post">
            @csrf
            @method('GET')

            <div class="modal-body">
               <small class="produk-info-harga badge bg-info d-block mb-2"></small>

               <label for="edit-harga-jual">Harga Jual</label>
               <div class="form-group">
                  <input type="text" id="edit-harga-jual" class="form-control" name="harga_jual"
                     placeholder="Masukkan harga jual" oninput="formatCurrency(this)" required>
               </div>
            </div>

            <div class="modal-footer pt-0">
               <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                  Batal
               </button>
               <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                  Update
               </button>
            </div>
         </form>

      </div>
   </div>
</div>