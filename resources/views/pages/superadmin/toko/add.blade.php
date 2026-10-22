@extends('layout.superadmin.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>{{ $title }}</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <form action="{{ route('superadmin.toko.store') }}" method="post" enctype="multipart/form-data">
               @csrf
               <label for="jumlah">Nama Toko</label>
               <div class="form-group">
                  <input type="text" id="nama_toko" class="form-control" name="nama_toko"
                     placeholder="Masukkan nama toko" required value="{{ old('nama_toko')}}">
               </div>
               <label for="alamat">Alamat</label>
               <div class="form-group">
                  <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}" required
                     placeholder="Alamat toko">
               </div>
               <label for="tiktok">Tiktok</label>
               <div class="form-group">
                  <input type="text" name="tiktok" class="form-control" value="{{ old('tiktok') }}"
                     placeholder="Akun Tiktok">
               </div>
               <label for="instagram">Instagram</label>
               <div class="form-group">
                  <input type="text" name="instagram" class="form-control" value="{{ old('instagram') }}"
                     placeholder="Akun Instagram">
               </div>
               <label for="logo">Logo</label>
               <div class="filepond--root image-preview-filepond filepond--hopper"
                  data-style-button-remove-item-position="left" data-style-button-process-item-position="right"
                  data-style-load-indicator-position="right" data-style-progress-indicator-position="right"
                  data-style-button-remove-item-align="false" style="height: 76px;">
                  <input class="filepond--browser" type="file" id="filepond--browser-id0etagp7"
                     aria-controls="filepond--assistant-id0etagp7" aria-labelledby="filepond--drop-label-id0etagp7"
                     accept="image/png,image/jpg,image/jpeg" name="filepond">
                  <div class="filepond--drop-label" style="transform: translate3d(0px, 0px, 0px); opacity: 1;"><label
                        for="filepond--browser-id0etagp7" id="filepond--drop-label-id0etagp7" aria-hidden="true">Drag
                        &amp; Drop your
                        files or <span class="filepond--label-action" tabindex="0">Browse</span></label></div>
                  <div class="filepond--list-scroller" style="transform: translate3d(0px, 0px, 0px);">
                     <ul class="filepond--list" role="list"></ul>
                  </div>
                  <div class="filepond--panel filepond--panel-root" data-scalable="true">
                     <div class="filepond--panel-top filepond--panel-root"></div>
                     <div class="filepond--panel-center filepond--panel-root"
                        style="transform: translate3d(0px, 8px, 0px) scale3d(1, 0.6, 1);"></div>
                     <div class="filepond--panel-bottom filepond--panel-root"
                        style="transform: translate3d(0px, 68px, 0px);"></div>
                  </div><span class="filepond--assistant" id="filepond--assistant-id0etagp7" role="status"
                     aria-live="polite" aria-relevant="additions"></span>
                  <fieldset class="filepond--data"></fieldset>
                  <div class="filepond--drip"></div>
               </div>

               <div class="mt-4 text-end">
                  <a href="{{ route('superadmin.toko') }}">
                     <button type="button" class="btn btn-outline-light rounded-pill">
                        <span class="d-sm-block">Kembali</span>
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