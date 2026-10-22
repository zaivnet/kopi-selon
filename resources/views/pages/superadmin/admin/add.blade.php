{{-- modal tambah admin --}}
<div class="modal fade text-left modal-borderless" id="modal-add-admin" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Admin</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{ route('superadmin.admin.store') }}" method="POST">
            @csrf
            <div class="modal-body">
               <label for="name">Nama Admin</label>
               <div class="form-group">
                  <input type="text" id="name" class="form-control" name="name" placeholder="Masukkan nama admin"
                     required autofocus>
               </div>
               <label for="username">Username</label>
               <div class="form-group">
                  <input type="text" id="username" class="form-control" name="username" placeholder="Masukkan username"
                     required>
               </div>
               <label for="email">Email</label>
               <div class="form-group">
                  <input type="email" id="email" class="form-control" name="email" placeholder="Masukkan email"
                     required>
               </div>
               <label for="password">Password</label>
               <div class="form-group">
                  <input type="text" id="password" class="form-control" name="password" placeholder="Masukkan password"
                     required autocomplete="off">
               </div>

               <label for="toko">Akses Toko</label>
               <div class="form-group">
                  <select name="toko[]" id="toko" class="choices form-select multiple-remove" multiple="multiple">
                     <optgroup label="Toko Tersedia">
                        @foreach ($daftarToko as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                     </optgroup>
                  </select>
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