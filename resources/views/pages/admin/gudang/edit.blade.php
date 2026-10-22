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
            <form action="{{ route('admin.produk.update', $produk->id) }}" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <label for="kode">Kode Produk</label>
                  <div class="d-flex justify-content-between">
                     <div class="form-group w-100">
                        <input type="text" id="kode" class="form-control" name="kode"
                           placeholder="Kode produk boleh dikosongi" value="{{old('kode', $produk->kode)}}" autofocus>
                     </div>
                     <button type="button" class="btn btn-primary" style="height: fit-content" id="scan-button">
                        <i class="bi bi-upc-scan"></i>
                     </button>
                  </div>

                  <label for="nama_produk">Nama Produk</label>
                  <div class="form-group">
                     <input type="text" id="nama_produk" class="form-control" name="nama_produk"
                        placeholder="Masukkan nama produk" value="{{old('nama_produk', $produk->nama_produk)}}" required
                        autofocus>
                  </div>
                  <label for="kategori">Kategori</label>
                  <fieldset class="form-group">
                     <select class="form-select" id="kategori" name="kategori" required>
                        <option value="" disabled selected>Pilih Kategori</option>
                        @foreach ($kategori as $item)
                        <option value="{{ $item->id }}" @if ($item->id == $produk->kategori_id)
                           selected
                           @endif>{{ $item->nama_kategori }}</option>
                        @endforeach
                     </select>
                  </fieldset>

                  <div class="modal-footer px-0 pt-2">
                     <a href="{{route('admin.gudang')}}">
                        <button type="button" class="btn btn-outline-light rounded-pill">
                           <span class="d-sm-block">Batal</span>
                        </button>
                     </a>
                     <button type="submit" class="btn btn-outline-primary rounded-pill ms-1">
                        <span class="d-sm-block">Update</span>
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