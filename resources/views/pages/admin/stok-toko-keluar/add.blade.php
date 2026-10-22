@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Tambah Stok Keluar Toko</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <form action=" {{ route('admin.stok-keluar.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <label for="produk">Produk</label>
                  <div class="form-group">
                     <select class="choices form-select" name="produk" id="produk" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produkToko as $namaProduk => $items)
                        <optgroup label="{{ $namaProduk }}">
                           @foreach ($items as $item)
                           <option value="{{ $item->produk_varian_id }}">
                              {{ $item->nama_varian }}
                           </option>
                           @endforeach
                        </optgroup>
                        @endforeach
                     </select>
                  </div>
                  <label for="qty_keluar">Stok Keluar</label>
                  <div class="form-group">
                     <input type="number" id="qty_keluar" class="form-control" name="qty_keluar"
                        value="{{old('qty_keluar')}}" placeholder="Masukkan jumlah stok keluar" required>
                  </div>
                  {{-- <label for="harga">Harga Produk</label>
                  <div class="form-group">
                     <input type="text" id="harga" class="form-control" name="harga" oninput="formatCurrency(this)"
                        placeholder="Masukkan harga produk">
                  </div> --}}

                  <div class="form-group mb-3">
                     <label for="keterangan" class="form-label">Deskripsi</label>
                     <textarea name="keterangan" class="form-control" id="keterangan" rows="2" autofocus required
                        placeholder="Berikan deskripsi">{{old('keterangan')}}</textarea>
                  </div>

                  <div class="modal-footer px-0">
                     <a href="{{route('admin.stok-keluar')}}">
                        <button type="button" class="btn btn-outline-light rounded-pill">
                           <span class="d-sm-block">Batal</span>
                        </button>
                     </a>
                     <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                        <span class="d-sm-block">Simpan</span>
                     </button>

                  </div>
            </form>
         </div>
      </div>
   </section>
</div>
@endsection