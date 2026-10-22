{{-- modal tambah relawan --}}
<div class="modal fade text-left modal-borderless" id="modal-add-shift" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Shift</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{ route('admin.shift.store', $tenant->id) }}" method="POST">
            @csrf
            <div class="modal-body">
               <label for="shift">Nama shift</label>
               <div class="form-group">
                  <input type="text" id="shift" class="form-control" name="shift" placeholder="Masukkan nama shift"
                     required autofocus>
               </div>
               <label for="start_time">Waktu Mulai</label>
               <div class="form-group">
                  <input type="time" id="start_time" step="1" class="form-control" name="start_time"
                     placeholder="Masukkan waktu mulai" required autofocus>
               </div>
               <label for="end_time">Waktu Selesai</label>
               <div class="form-group">
                  <input type="time" id="end_time" step="1" class="form-control" name="end_time"
                     placeholder="Masukkan waktu selesai" required autofocus>
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