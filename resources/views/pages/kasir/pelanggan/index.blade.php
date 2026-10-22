@extends('layout.kasir.app')
@section('content')

@include('pages.kasir.pelanggan.tukar-poin')
@include('pages.kasir.pelanggan.edit')
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Data Pelanggan</h3>
         </div>
      </div>
   </div>
   <div class="card">
      <div class="card-body">
         <div class="d-flex justify-content-end">
            <button class="btn btn-outline-primary mb-3 rounded-pill" data-bs-toggle="modal"
               data-bs-target="#modal-add-pelanggan">
               Tambah
            </button>
         </div>
         <div class="table-responsive">
            <table class="table table-striped" id="table1">
               <thead>
                  <tr>
                     <th>No</th>
                     <th>Nama Pelanggan</th>
                     <th>Telepon/Wa</th>
                     <th>Alamat</th>
                     <th>Poin</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($pelanggan as $item)
                  <tr>
                     <td>{{ $loop->iteration }}</td>
                     <td>{{ $item->nama }}</td>
                     <td>{{ $item->telepon }}</td>
                     <td>{{ $item->alamat }}</td>
                     <td>{{ $item->poin }}</td>
                     <td>
                        <div class="d-flex flex-row gap-1">
                           <button style="width: 40px; height:40px;"
                              class="btn btn-outline-warning btn-md rounded-pill d-flex justify-content-center btn-edit-pelanggan"
                              data-id="{{ $item->id }}" data-nama="{{$item->nama}}" data-alamat="{{$item->alamat}}"
                              data-whatsapp="{{$item->telepon}}">
                              <i class=" bi bi-pencil p-0"></i>
                           </button>
                           <button type="button" class="btn btn-sm btn-outline-success rounded-pill editPelanggan"
                              data-id_pelanggan="{{ $item->id }}" data-nama="{{ $item->nama }}"
                              data-alamat="{{ $item->alamat }}" data-telepon="{{ $item->telepon }}"
                              data-poin="{{ $item->poin }}">Tukar Poin
                           </button>
                        </div>

                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
@include('pages.kasir.pelanggan.add')
@endsection

{{-- @section('scripts')
<script>
   $(document).ready(function() {
        $('.editPelanggan').click(function(e) {
            e.preventDefault();

            $('#modalPelanggan').modal('show');
            $('#modalPelanggan .modal-title').text('Penukaran Poin Pelanggan');
            $('#methodField').val('PUT');

            let idPelanggan = $(this).data('id_pelanggan');
            let nama = $(this).data('nama');
            let alamat = $(this).data('alamat');
            let telepon = $(this).data('telepon');
            let poin = $(this).data('poin');

            $('#nama').text(nama);
            $('#alamat').text(alamat);
            $('#telepon').text(telepon);
            $('#poin-text').text(poin);

            $('#poin_ditukar').on('input', function() {
                let poin_input = $(this).val();
                if (poin_input <= poin) {
                    let sisa_poin = poin - poin_input;
                    $('#poin-text').text(sisa_poin);
                } else {
                    $('#poin-text').text(poin);
                    $('#poin_ditukar').val('');
                }
            })

            $('#formPelanggan').attr('action', '/prosesPenukaranPoin/' + idPelanggan);
        });
    });
</script>
@endsection --}}