@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Edit Stok Keluar Gudang</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <form action=" {{ route('admin.gudang.stok-keluar.update', $stokKeluar->id) }}" method="POST">
               @csrf
               @method('GET')
               <div class="modal-body">
                  <label for="produk">Produk</label>
                  <div class="form-group">
                     <input type="text" id="produk" class="form-control" name="produk" placeholder="Nama produk"
                        required disabled value="{{old('produk',$produk->nama_produk)}}">
                  </div>
                  <label for="qty_keluar">Stok Keluar</label>
                  <div class="form-group">
                     <input type="number" id="qty_keluar" class="form-control" name="qty_keluar"
                        placeholder="Masukkan jumlah stok keluar" required
                        value="{{old('qty_keluar', $stokKeluar->qty_keluar)}}">
                  </div>

                  <div class="form-group mb-3">
                     <label for="keterangan" class="form-label">Deskripsi</label>
                     <textarea name="keterangan" class="form-control" id="keterangan" rows="2" autofocus required
                        placeholder="Berikan deskripsi">{{old('keterangan', $stokKeluar->keterangan)}}</textarea>
                  </div>

                  <div class="modal-footer px-0">
                     <a href="{{route('admin.gudang.stok-keluar')}}">

                        <button type="button" class="btn btn-outline-light rounded-pill">
                           <span class="d-sm-block">Batal</span>
                        </button>
                     </a>
                     <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                        <span class="d-sm-block">Update</span>
                     </button>

                  </div>
            </form>
         </div>
      </div>
   </section>
</div>
@endsection