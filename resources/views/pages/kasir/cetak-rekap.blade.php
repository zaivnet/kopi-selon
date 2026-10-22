<style>
   /* #print-area {
      width: 180px;
   } */

   table {
      width: 100%;
   }

   .center {
      text-align: center;
   }

   .right {
      text-align: right;
   }

   .line {
      border-top: 1px dashed #000;
      margin: 6px 0;
   }

   .row {
      display: flex;
      justify-content: space-between;
   }

   .bold {
      font-weight: bold;
   }

   .mt {
      margin-top: 6px;
   }

   .fs-sm {
      font-size: 8px;
   }

   .fs-lg {
      font-size: 14px;
   }

   .header {
      font-size: 14px;
   }
</style>

<div id="print-area">
   <table>
      <tr>
         <td colspan="2" class="center header">
            <strong>{{ $tenant->name }}</strong><br>
            Tanggal : {{ \Carbon\Carbon::parse($tanggalShift)->format('d/m/Y') }}
         </td>
      </tr>
      <tr>
         <td>Shift {{ $nama_shift }}</td>
         <td class="right">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</td>
      </tr>
      <tr>
         <td colspan="2">
            <hr>
         </td>
      </tr>
      <tr>
         <td>Item Terjual</td>
         <td align="right">{{ $penjualan->total_item ?? 0 }} item</td>
      </tr>
      <tr>
         <td>Bayar Tunai</td>
         <td align="right">Rp {{number_format($totalTunai, 0, ',', '.')}}</td>
      </tr>
      <tr>
         <td>Bayar QRIS</td>
         <td align="right">Rp {{number_format($totalQris, 0, ',', '.')}}</td>
      </tr>
      <tr>
         <td>Total Penjualan</td>
         <td align="right">Rp {{ number_format($penjualan->total_harga, 0, ',', '.' ?? 0) }}</td>
      </tr>

      <tr>
         <td colspan="2">
            <hr>
         </td>
      </tr>

      <tr>
         <td>Pengeluaran</td>
         <td align="right">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</td>
      </tr>

      <tr class="fs-lg">
         <td><strong>Laba Tunai</strong></td>
         <td align="right"><strong>Rp {{ number_format($labaTunai, 0, ',', '.') }}</strong></td>
      </tr>

      <tr>
         <td colspan="2">
            <hr>
         </td>
      </tr>

      <tr>
         <td>Barang Keluar</td>
         <td align="right">{{ $retur->total_item_retur ?? 0 }} item</td>
      </tr>

      <tr>
         <td>Harga Keluar</td>
         <td align="right">Rp {{ number_format($retur->total_harga_retur, 0, ',', '.' ?? 0) }}</td>
      </tr>

      <tr>
         <td colspan="2">
            <hr>
         </td>
      </tr>

      <tr class="fs-lg">
         <td><strong>Laba Bersih</strong></td>
         <td align="right"><strong>Rp {{ number_format($labaBersih, 0, ',', '.') }}</strong></td>
      </tr>

      <tr>
         <td colspan="2">
            <hr>
         </td>
      </tr>

      <tr>
         <td colspan="2" align="center">
            <div class="center mt fs-sm">
               Dicetak:
               {{ now()->format('d/m/Y H:i') }}
            </div>

            <div class="center">
               Kasir: {{ auth()->user()->name }}
            </div>
         </td>
      </tr>
   </table>
</div>

