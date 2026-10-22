@extends('layout.admin.app')
@section('content')
<style>
   .modal-body {
      max-height: 70vh;
      overflow-y: auto;
   }

   .modal-footer {
      position: sticky;
      bottom: 0;
      background: #fff;
      z-index: 1;
   }
</style>
<div class="page-heading">
   <div class="page-title">
      <div class="row">
         <div class="col-12 col-md-6 order-md-1 order-last mt-10">
            <h3>Produk</h3>
         </div>
      </div>
   </div>

   @include('pages.admin.produk-toko.edit-harga')
   @include('pages.admin.produk-toko.delete')

   <section class="section">
      <div class="card">
         <div class="card-body">

            <div class="table-responsive">

               <table class="table table-striped" id="table1">
                  <thead>
                     <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Varian</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($produk as $index => $item)
                     @php
                     // $pivot = $item->tenant->first()?->pivot;
                     @endphp
                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_produk }}</td>
                        <td>{{ $item->kategori->nama_kategori }}</td>
                        {{-- VARIAN --}}
                        <td>
                           @if ($item->toko->exists)
                           {{ $item->toko->varian_aktif }} varian
                           @else
                           <span class="text-muted">-</span>
                           @endif
                        </td>
                        <td>
                           @if ($item->toko->exists)
                           <span
                              class="text-sm badge {{$item->toko->stok == 0 ? 'bg-danger' : ($item->toko->stok <= 30 ? 'bg-warning text-dark' : 'bg-success')}}">
                              {{ $item->toko->stok }}
                              @else
                              <span class="text-muted">Belum ada</span>
                              @endif
                        </td>
                        <td>

                           @if (! $item->toko->exists)
                           <span>
                              Belum ditampilkan
                           </span>
                           @elseif ($item->toko->display)
                           <span class="badge bg-success">
                              Tampil
                           </span>
                           @else
                           <span class="badge bg-secondary">
                              Nonaktif
                           </span>
                           @endif
                        </td>
                        <td>
                           <div class="d-flex flex-row gap-1">
                              <button style="width: 40px; height:40px;"
                                 class="btn btn-outline-info btn-md rounded-pill d-flex justify-content-center btn-tambah-produk-toko"
                                 data-bs-toggle="modal" data-bs-target="#modal-add-produk-toko"
                                 data-id="{{ $item->id }}" data-produk="{{ $item->nama_produk }}"
                                 data-harga="{{ $item->toko->harga_jual ?? 0 }}"
                                 data-has-varian="{{ ($item->toko->jumlah_varian) }}">
                                 <i class=" bi bi-plus p-0"></i>
                              </button>
                              @if ($item->toko->display)
                              <a href="{{ route('admin.toko.produk.toggle-display', $item->id) }}">
                                 <button style="width: 40px; height:40px; padding-top: 8px"
                                    class="btn btn-sm btn-outline-danger rounded-pill d-flex justify-content-center">
                                    <i class=" bi bi-eye-slash-fill p-0"></i>
                                 </button>
                              </a>
                              @else
                              <a href="{{ route('admin.toko.produk.toggle-display', $item->id) }}">
                                 <button style="width: 40px; height:40px; padding-top: 8px"
                                    class="btn btn-sm btn-outline-secondary rounded-pill d-flex justify-content-center">
                                    <i class=" bi bi-eye-fill p-0"></i>
                                 </button>
                              </a>
                              @endif
                              <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal"
                                 data-bs-target="#modal-varian"
                                 data-produk-toko-id="{{ $item->produkToko->first()?->id }}">Varian
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
   </section>
   @include('pages.admin.produk-toko.modal-varian')
   @include('pages.admin.produk-toko.add')

</div>
<script>
   function updateRemoveButton() {
      const rows = document.querySelectorAll('#varianBody tr');
      const isSingle = rows.length <= 1; rows.forEach(row=> {
         const btn = row.querySelector('.btn-remove');
         if (btn) {
            btn.disabled = isSingle;
         }
      });
   }

   const modalVarian = document.getElementById('modal-varian');
   
   modalVarian.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget; // tombol yg diklik
      const produkTokoId = button.getAttribute('data-produk-toko-id');
      
      document.getElementById('produk_toko_id').value = produkTokoId;
      document.getElementById('varianBody').innerHTML = '';
      
      fetch(`/toko/produk/${produkTokoId}/varian`)
      .then(res => res.json())
      .then(data => {
         data.forEach(v => appendRow(v));
         updateRemoveButton();
      });
   });

   function appendRow(data = {}) {
      const key = Date.now() + Math.random();
   
      document.getElementById('varianBody').insertAdjacentHTML('beforeend', `
         <tr>
            <td>
               <input type="hidden" name="varians[${key}][id]" value="${data.id ?? ''}">
               <input type="text" class="form-control" name="varians[${key}][nama]" value="${data.nama ?? ''}" required placeholder="Nama varian">
            </td>
            <td>
               <input type="text" class="form-control harga" name="varians[${key}][harga_jual]"
                  value="${data.harga_jual ? formatRupiahBaru(data.harga_jual) : ''}" placeholder="Rp 0" required oninput="this.value = formatRupiah(this.value)">
            </td>
            <td class="text-center">
               <input type="checkbox" name="varians[${key}][is_active]" ${data.is_active ? 'checked' : '' }>
            </td>
            <td>
               <button type="button" class="btn btn-sm btn-danger btn-remove">✕</button>
            </td>
         </tr>
      `);
   }
   
   document.getElementById('btnAddVarian').addEventListener('click', () => {
      appendRow();
      updateRemoveButton();
   });
   
   document.addEventListener('click', function (e) {
      if (e.target.classList.contains('btn-remove')) {
         e.target.closest('tr').remove();
         updateRemoveButton();
      }
   });
</script>

<script>
   document.getElementById('formVarian').addEventListener('submit', function (e) {
    e.preventDefault();

    // bersihkan format rupiah
    document.querySelectorAll('.harga').forEach(input => {
        input.value = input.value.replace(/[^\d]/g, '');
    });

    fetch("{{ route('admin.produk.varian.store') }}", {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => {
        if (!res.ok) throw res;
        return res.json();
    })
    .then(res => {
       
       // opsi 1: reload (paling aman)
       setTimeout(() => {
          location.reload();
         }, 800);
         
      toastr.success(res.message, 'Berhasil');
      // opsi 2: tutup modal saja (kalau table auto refresh via JS)
      // $('#modal-varian').modal('hide');
      })
    .catch(async err => {
      let msg = 'Terjadi kesalahan';
      
      try {
         const data = await err.json();
          msg = data.message ?? msg;
      } catch {}
      
      toastr.error(msg, 'Gagal');
      });
});
</script>
@endsection