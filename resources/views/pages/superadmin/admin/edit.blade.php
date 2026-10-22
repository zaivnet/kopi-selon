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

            <form action="{{ route('superadmin.admin.update', $admin->id) }}" method="POST">
               @csrf
               @method('GET')
               <div class="modal-body">
                  <label for="name-edit">Nama Admin</label>
                  <div class="form-group">
                     <input type="text" id="name-edit" class="form-control" name="name"
                        placeholder="Masukkan nama admin" value="{{$admin->name}}" required autofocus>
                  </div>
                  <label for="username-edit">Username</label>
                  <div class="form-group">
                     <input type="text" id="username-edit" class="form-control" name="username"
                        placeholder="Masukkan username" value="{{$admin->username}}" required>
                  </div>
                  <label for="email-edit">Email</label>
                  <div class="form-group">
                     <input type="email" id="email-edit" class="form-control" name="email" placeholder="Masukkan email"
                        value="{{$admin->email}}" required>
                  </div>
                  <label for="password-edit">Password</label>
                  <div class="form-group">
                     <input type="text" id="password-edit" class="form-control" name="password"
                        placeholder="Masukkan password baru" autocomplete="off">
                  </div>

                  <label for="toko-edit">Akses Toko</label>
                  <div class="form-group">
                     <select name="toko[]" id="toko-edit" class="choices form-select multiple-remove"
                        multiple="multiple">
                        <optgroup label="Toko Tersedia">
                           @foreach ($daftarToko as $item)
                           <option value="{{(string) $item->id }}" @if ($admin->tenants->contains($item->id)) selected
                              @endif>{{ $item->name }}</option>
                           @endforeach
                        </optgroup>
                     </select>
                  </div>

                  <div class="mt-4 text-end">
                     <a href="{{route('superadmin.admin')}}">
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