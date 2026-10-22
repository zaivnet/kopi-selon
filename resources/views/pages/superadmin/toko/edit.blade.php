@extends('layout.superadmin.app')
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
            <div class="row mb-3 d-flex justify-content-center">
               <img @if ($data->logo)
               src="{{asset('assets/logo/'.$data->logo)}}"
               @else
               src="{{asset('assets/logo/thumbnail.png')}}"
               @endif
               alt="{{$data->name}}" srcset=""
               style="max-width: 200px; max-height:200px;">
            </div>
            <form action="{{ route('superadmin.toko.update', $data->id) }}" method="post" enctype="multipart/form-data">
               @csrf
               @method('GET')

               <label for="nama_toko">Nama Toko</label>
               <div class="form-group">
                  <input type="text" id="nama_toko" class="form-control" name="nama_toko"
                     placeholder="Masukkan nama toko" autofocus required value="{{ old('nama_toko', $data->name)}}">
               </div>
               <label for="alamat">Alamat</label>
               <div class="form-group">
                  <input type="text" name="alamat" id="alamat" class="form-control"
                     value="{{ old('alamat', $data->address) }}" required placeholder="Alamat toko">
               </div>
               <label for="tiktok">Tiktok</label>
               <div class="form-group">
                  <input type="text" name="tiktok" id="tiktok" class="form-control"
                     value="{{ old('tiktok', $data->tiktok) }}" placeholder="Akun Tiktok">
               </div>
               <label for="instagram">Instagram</label>
               <div class="form-group">
                  <input type="text" name="instagram" id="instagram" class="form-control"
                     value="{{ old('instagram', $data->instagram) }}" placeholder="Akun Instagram">
               </div>
               <div class="form-check form-switch mt-3">
                  <input class="form-check-input" name="status" type="checkbox" id="status" @if ($data->isActive)
                  checked @endif>
                  <label class="form-check-label" for="status">Aktif/Nonaktif</label>
               </div>
               <label for="filepond--browser-id0etagp7">Logo</label>
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
                     <span class="d-sm-block">Update</span>
                  </button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
@endsection