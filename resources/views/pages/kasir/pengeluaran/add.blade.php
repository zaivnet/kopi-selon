@extends('layout.kasir.app')
@section('content')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Tambah Pengeluaran</h3>
         </div>
      </div>
   </div>

   <section class="section">
      <div class="card">
         <div class="card-body">
            <form action="{{ route('kasir.pengeluaranToko.store') }}" method="POST">
               @csrf

               <div class="form-group mb-3">
                  <label for="deskripsi" class="form-label">Deskripsi</label>
                  <textarea name="deskripsi" class="form-control" id="deskripsi" rows="2" autofocus required
                     placeholder="Berikan deskripsi pengeluaran">{{old('deskripsi')}}</textarea>
               </div>

               <label for="jumlah">Jumlah Pengeluaran (Rp)</label>
               <div class="form-group">
                  <input type="text" id="jumlah" class="form-control" name="jumlah"
                     placeholder="Masukkan jumlah pengeluaran" required oninput="formatCurrency(this)"
                     value="{{old('jumlah')}}">
               </div>

               <div class="mt-4 text-end">
                  <a href="{{ route('admin.pengeluaran') }}">
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