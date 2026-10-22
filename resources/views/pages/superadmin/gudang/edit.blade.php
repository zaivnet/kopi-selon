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

            <form action="{{ route('superadmin.gudang.update', $gudang->id) }}" method="POST">
               @csrf
               @method('GET')
               <div class="modal-body">
                  <label for="gudang">Nama Gudang</label>
                  <div class="form-group">
                     <input type="text" id="gudang" class="form-control" name="gudang"
                        placeholder="Masukkan nama gudang" value="{{$gudang->name}}" required autofocus>
                  </div>

                  <label for="toko-edit">Akses Toko</label>
                  <div class="form-group">
                     <select name="toko[]" id="toko-edit" class="choices form-select multiple-remove"
                        multiple="multiple">
                        <optgroup label="Toko Tersedia">
                           @foreach ($daftarToko as $item)
                           <option value="{{(string) $item->id }}" @if ($gudang->tenants->contains($item->id)) selected
                              @endif>{{ $item->name }}</option>
                           @endforeach
                        </optgroup>
                     </select>
                  </div>

                  <div class="mt-4 text-end">
                     <a href="{{route('superadmin.gudang')}}">
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
   </section>
</div>
@endsection