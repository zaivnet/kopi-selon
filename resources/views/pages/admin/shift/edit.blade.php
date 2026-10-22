{{-- modal edit shift --}}
<div class="modal fade text-left modal-borderless" id="modal-edit-shift" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-update-template="{{ url('/toko/shift/update/:id') }}">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Edit Shift</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>

         <form id="formEditShift" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <input type="hidden" name="id-shift-edit" id="id-shift-edit">
               <label for="shift">Nama Shift</label>
               <div class="form-group">
                  <input type="text" id="shift_edit" class="form-control" name="shift" placeholder="Masukkan nama shift"
                     required autofocus>
               </div>
               <label for="start_time">Waktu Mulai</label>
               <div class="form-group">
                  <input type="time" id="start_time_edit" step="1" class="form-control" name="start_time"
                     placeholder="Masukkan waktu mulai" required autofocus>
               </div>
               <label for="end_time">Waktu Selesai</label>
               <div class="form-group">
                  <input type="time" id="end_time_edit" step="1" class="form-control" name="end_time"
                     placeholder="Masukkan waktu selesai" required autofocus>
               </div>

               <div class="modal-footer px-0">
                  <button type="reset" class="btn btn-outline-light rounded-pill" data-bs-dismiss="modal">
                     <span class="d-sm-block">Batal</span>
                  </button>
                  <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                     <span class="d-sm-block">Update</span>
                  </button>
               </div>
            </div>
         </form>

      </div>
   </div>
</div>