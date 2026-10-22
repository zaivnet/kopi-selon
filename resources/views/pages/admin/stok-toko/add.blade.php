{{-- modal tambah stok--}}
<div class="modal fade text-left modal-borderless" id="modal-add-stok" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Stok</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{route('admin.stok.store')}}" method="POST">
            @csrf
            <div class="modal-body">
               <small class="produk-info badge bg-info d-block mb-2"></small>

               <input type="hidden" name="id_produk" id="id_produk">

               <label for="stok_masuk">Stok Masuk</label>
               <div class="form-group">
                  <input type="text" id="stok_masuk" class="form-control" name="stok_masuk"
                     placeholder="Masukkan jumlah stok" required>
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