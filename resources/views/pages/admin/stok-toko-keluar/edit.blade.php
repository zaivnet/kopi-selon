{{-- modal tambah stok--}}
<div class="modal fade text-left modal-borderless" id="modal-edit-stok-keluar" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true"
   data-update-template="{{ url('/toko/produk/stok-keluar/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Stok Toko Keluar</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form id="formEditStokKeluar" action="" method="POST">
            @csrf
            @method('GET')
            <div class="modal-body">
               <small class="produk-info badge bg-info d-block mb-2"></small>

               <label>Stok Keluar</label>
               <input type="number" name="qty_keluar" id="stok_keluar" class="form-control" min="1" required
                  placeholder="Masukkan Jumlah Stok Keluar">

               <div class="form-group mb-3">
                  <label for="keterangan" class="form-label">Deskripsi</label>
                  <textarea name="keterangan" class="form-control" id="keterangan" rows="2" autofocus required
                     placeholder="Berikan deskripsi">{{old('keterangan')}}</textarea>
               </div>

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