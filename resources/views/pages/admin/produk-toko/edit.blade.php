<div class="modal fade text-left modal-borderless" id="modal-edit-produk" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/produk/update/:id') }}">

   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">

         <div class="modal-header">
            <h5 class="modal-title">Edit Produk</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditProduk" action="" method="post">
            @csrf
            @method('PUT')

            <div class="modal-body">
               <input type="hidden" name="id-produk" id="id-produk">

               <label for="edit-nama-menu">Nama Produk</label>
               <div class="form-group">
                  <input type="text" id="edit-nama-menu" class="form-control" name="nama_menu"
                     placeholder="Masukkan nama menu" required autofocus>
               </div>

               <label for="kategori">Kategori</label>
               <fieldset class="form-group">
                  <select class="form-select" id="kategori" name="kategori" required>
                     <option value="" disabled selected>Pilih Kategori</option>
                     @foreach ($kategori as $item)
                     <option value="{{ $item->id }}">{{ $item->nama_kategori }}</option>
                     @endforeach
                  </select>
               </fieldset>

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
                  Simpan
               </button>
            </div>
         </form>

      </div>
   </div>
</div>