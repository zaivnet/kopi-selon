{{-- modal edit printer --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-printer" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/printer/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Printer</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditPrinter" action="" method="POST">
            @csrf
            @method('GET')
            <div class="modal-body">
               <label for="printer-edit">Nama Printer</label>
               <div class="form-group">
                  <input type="text" id="printer-edit" class="form-control" name="printer"
                     placeholder="Masukkan nama printer" required autofocus>
               </div>

               <label for="kertas-edit">Ukuran Kertas (mm)</label>
               <div class="form-group">
                  <input type="number" id="kertas-edit" class="form-control" name="kertas"
                     placeholder="Masukkan ukuran kertas" min="1" required autofocus>
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