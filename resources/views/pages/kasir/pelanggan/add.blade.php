{{-- modal tambah pelanggan --}}
<div class="modal fade text-left modal-borderless" id="modal-add-pelanggan" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Pelanggan</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{route('kasir.pelanggan.store')}}" method="POST">
            @csrf
            <div class="modal-body">
               <label for="nama">Nama Pelanggan</label>
               <div class="form-group">
                  <input type="text" id="nama" class="form-control" name="nama" placeholder="Masukkan nama pelanggan"
                     required autofocus>
               </div>
               <label for="alamat">Alamat</label>
               <div class="form-group">
                  <input type="text" id="alamat" class="form-control" name="alamat" placeholder="Alamat boleh dkosongi"
                     autofocus>
               </div>
               <label for="whatsapp">No. Whatsapp</label>
               <div class="form-group">
                  <input type="text" id="whatsapp" class="form-control" name="whatsapp"
                     placeholder="Nomor whatsapp pelanggan" required autofocus>
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