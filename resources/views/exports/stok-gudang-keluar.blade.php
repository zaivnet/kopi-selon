<table>
   <tr>
      <td colspan="5"><strong>Laporan Stok Gudang Keluar</strong></td>
   </tr>
   <tr>
      <td colspan="5">
         Gudang:
         {{ $gudangNames->count() === 1 ? $gudangNames->first() : 'Multi Gudang' }}
      </td>
   </tr>
   <tr>
      <td colspan="5">
         Periode: {{ request('bulan') ?? now()->format('Y-m') }}
      </td>
   </tr>
</table>

<br>

<table>
   <thead>
      <tr>
         <th><strong>No</strong></th>
         <th><strong>Tanggal</strong></th>
         <th><strong>Nama Produk</strong></th>
         <th><strong>Jumlah Keluar</strong></th>
         <th><strong>Keterangan</strong></th>
         <th><strong>Admin</strong></th>
      </tr>
   </thead>
   <tbody>
      @foreach ($data as $item)
      <tr>
         <td>{{ $loop->iteration }}</td>
         <td>{{ $item->created_at->format('d-m-Y') }}</td>
         <td>{{ $item->produk->nama_produk ?? '-' }}</td>
         <td>{{ $item->qty_keluar }}</td>
         <td>{{ $item->keterangan }}</td>
         <td>{{ $item->admin->name ?? '-' }}</td>
      </tr>
      @endforeach

      {{-- TOTAL --}}
      <tr>
         <td colspan="6">
            <h5>Total Keluar : {{ $total->total_qty_keluar }} Item</h5>
         </td>
      </tr>
   </tbody>
</table>