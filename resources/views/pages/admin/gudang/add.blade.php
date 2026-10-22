@extends('layout.admin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>{{$title}}</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <form action="{{ route('admin.produk.store', $tenant->id) }}" method="POST">
               @csrf
               <div class="modal-body">
                  <label for="kode">Kode Produk</label>
                  <div class="d-flex justify-content-between">
                     <div class="form-group w-100">
                        <input type="text" id="kode" class="form-control" name="kode"
                           placeholder="Kode produk boleh dikosongi" autofocus>
                     </div>
                     <button type="button" class="btn btn-primary" style="height: fit-content" id="scan-button">
                        <i class="bi bi-upc-scan"></i>
                     </button>
                  </div>

                  <label for="nama_produk">Nama Produk</label>
                  <div class="form-group">
                     <input type="text" id="nama_produk" class="form-control" name="nama_produk"
                        placeholder="Masukkan nama produk" required autofocus>
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

                  <label for="stok_awal">Stok Awal</label>
                  <div class="form-group">
                     <input type="number" id="stok_awal" class="form-control" name="stok_awal"
                        placeholder="Masukkan jumlah stok awal" required>
                  </div>

                  <label for="harga_beli">Harga Pembelian</label>
                  <div class="form-group">
                     <input type="text" id="harga_beli" class="form-control" name="harga_beli"
                        placeholder="Masukkan harga beli untuk menu ini" oninput="formatCurrency(this)" required>
                  </div>

                  <div class="modal-footer px-0 pt-2">
                     <a href="{{route('admin.gudang')}}">
                        <button type="button" class="btn btn-outline-light rounded-pill">
                           <span class="d-sm-block">Batal</span>
                        </button>
                     </a>
                     <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                        <span class="d-sm-block">Simpan</span>
                     </button>
                  </div>
               </div>
            </form>
         </div>
      </div>

      @include('pages.admin.gudang.scan')
   </section>
</div>
@endsection