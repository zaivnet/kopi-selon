{{-- modal tambah gudang --}}
<div class="modal fade text-left modal-borderless" id="modal-add-gudang" tabindex="-1" role="dialog"
   aria-labelledby="myModalLabel1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Tambah Gudang</h5>
            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
               <i data-feather="x"></i>
            </button>
         </div>
         <form action="{{route('superadmin.gudang.store')}}" method="POST">
            @csrf
            <div class="modal-body">
               <label for="gudang">Nama Gudang</label>
               <div class="form-group">
                  <input type="text" id="gudang" class="form-control" name="gudang" placeholder="Masukkan nama gudang"
                     required autofocus>
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
               {{-- <div class="form-check form-switch mt-3">
                  <input class="form-check-input" name="status" type="checkbox" id="status">
                  <label class="form-check-label" for="status">Aktif/Nonaktif</label>
               </div> --}}

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