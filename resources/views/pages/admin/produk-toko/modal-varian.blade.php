<div class="modal fade text-left modal-borderless" id="modal-varian" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Varian</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form id="formVarian">
            @csrf
            <input type="hidden" name="produk_toko_id" id="produk_toko_id">

            <div class="modal-content">
               <div class="modal-body">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>Nama Varian</th>
                           <th>Harga</th>
                           <th>Aktif</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody id="varianBody"></tbody>
                  </table>
                  <button type="button" class="btn btn-sm btn-secondary" id="btnAddVarian">
                     + Tambah Varian
                  </button>
               </div>

               <div class="modal-footer">
                  <button type="submit" class="btn btn-outline-primary rounded-pill">
                     Simpan
                  </button>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>