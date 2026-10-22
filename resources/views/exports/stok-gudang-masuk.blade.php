<table>
   <tr>
      <td colspan="8"><strong>Laporan Stok Gudang Masuk</strong></td>
   </tr>
   <tr>
      <td colspan="8">Gudang:
         {{ $gudangNames->count() === 1 ? $gudangNames->first() : 'Multi Gudang' }}</td>
   </tr>
   <tr>
      <td colspan="8">Periode: {{ request('bulan') ?? now()->format('Y-m') }}</td>
   </tr>
</table>

<br>
<table>
   <thead>
      <tr>
         <th><strong>No</strong></th>
         <th><strong>Tanggal</strong></th>
         <th><strong>Kode Produk</strong></th>
         <th><strong>Nama Produk</strong></th>
         <th><strong>Jumlah Masuk</strong></th>
         <th><strong>Harga Pembelian</strong></th>
         <th><strong>Gudang</strong></th>
         <th><strong>Admin</strong></th>
      </tr>
   </thead>
   <tbody>
      @foreach ($data as $item)
      <tr>
         <td>{{ $loop->iteration }}</td>
         <td>{{ $item->created_at->format('d-m-Y') }}</td>
         <td>{{ $item->produk->kode ?? '-' }}</td>
         <td>{{ $item->produk->nama_produk ?? '-' }}</td>
         <td>{{ $item->qty_masuk }}</td>
         <td>{{ $item->harga_pembelian }}</td>
         <td>{{ $item->gudang->name ?? '-' }}</td>
         <td>{{ $item->admin->name ?? '-' }}</td>
      </tr>
      @endforeach
      {{-- ROW TOTAL --}}
      <tr>
         <td colspan="8">
            <h5>Total : {{$total->total_qty_masuk}} Item</h5>
         </td>
      </tr>
      <tr>
         <td colspan="8">
            <h5>Harga : Rp{{number_format($total->total_harga_pembelian,0,',','.')}}</h5>
         </td>
      </tr>
   </tbody>
</table>