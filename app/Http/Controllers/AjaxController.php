<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use App\Models\DetailTransaksi;
use App\Models\ProdukToko;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AjaxController extends Controller
{
   //
   public function getTetantId()
   {
      $getTenantId = session('active_tenant_id');
      return $getTenantId;
   }

   public function getProduk(Request $request)
   {
      $query = DB::table('produk_varian')
         ->join('produk_toko', 'produk_varian.produk_toko_id', '=', 'produk_toko.id')
         ->join('produk', 'produk_toko.produk_id', '=', 'produk.id')
         ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
         ->select(
            'produk_varian.id as produk_varian_id',           // 🔥 ID VARIAN
            'produk_toko.id as produk_toko_id',
            'produk.id as produk_id',
            'produk.kode as produk_kode',
            'produk.nama_produk as nama_produk',
            'produk_varian.nama as nama_varian',              // 🔥 NAMA VARIAN
            'kategori.nama_kategori as nama_kategori',
            'produk.kategori_id',
            'produk_varian.harga_jual as harga_jual',         // 🔥 HARGA DARI VARIAN
            'produk_toko.stok as stok'
         )
         ->where('produk_toko.tenant_id', $this->getTetantId())
         ->where('produk_toko.display', 1)                    // 🔥 PRODUK TOKO AKTIF
         ->where('produk_varian.is_active', 1)                // 🔥 VARIAN AKTIF
         ->orderBy('produk_toko.id', 'ASC');

      // 🔍 SEARCH (nama produk / kode / nama varian)
      if ($request->filled('search')) {
         $search = $request->search;

         $query->where(function ($q) use ($search) {
            $q->where('produk.nama_produk', 'LIKE', "%{$search}%")
               ->orWhere('produk.kode', 'LIKE', "%{$search}%")
               ->orWhere('produk_varian.nama', 'LIKE', "%{$search}%");
         });
      }

      // 🏷 FILTER KATEGORI
      if ($request->filled('kategori_id')) {
         $query->where('produk.kategori_id', $request->kategori_id);
      }

      $getProduk = $query->paginate(8);

      return response()->json($getProduk);
   }


   public function cekStokProdukTransaksi($id)
   {
      $produkToko = ProdukToko::where('id', $id)
         ->where('tenant_id', $this->getTetantId())
         ->first();

      return response()->json([
         'status' => 'success',
         'stok' => $produkToko->stok ?? 0
      ]);
   }

   public function updateStatusTransaksi(Request $request, $id)
   {
      $status = $request->status;

      $transaksi = Transaksi::findOrFail($id);

      $transaksi->update([
         'status' => $status
      ]);

      return response()->json([
         'status' => 'success',
         'msg' => 'Status transaksi berhasil di update'
      ]);
   }

   public function getDetailTransaksi($id)
   {
      $detailTransaksi = DetailTransaksi::join('produk_varian', 'produk_varian.id', '=', 'detail_transaksis.produk_varian_id')
         ->join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
         ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
         ->select(
            'detail_transaksis.id as id_detail_transaksi',
            'detail_transaksis.jumlah',
            'detail_transaksis.harga_satuan',
            'detail_transaksis.total_harga_produk',
            'produk_toko.id as id_produk_toko',
            'produk_toko.produk_id',
            'produk.nama_produk',
            'produk_varian.id as id_varian',
            'produk_varian.nama as nama_varian'
         )
         ->where('transaksi_id', $id)->get();

      $transaksi = Transaksi::findOrFail($id);

      $poinPelanggan = '';
      if ($transaksi->pelanggan_id != 0) {
         $pelanggan = Pelanggan::where('id', $transaksi->pelanggan_id)
            ->first();
         $poinPelanggan = 'Poin pelanggan sekarang : ' . $pelanggan->poin;
      } else {
         $poinPelanggan = 'Gabung sebagai pelanggan untuk mendapatkan poin';
      }

      return response()->json([
         'status' => 'success',
         'data' => $detailTransaksi,
         'poin_pelanggan' => $poinPelanggan
      ]);
   }

   public function getDataPelanggan()
   {
      $data = Pelanggan::all();

      return response()->json($data);
   }

   public function filterTransaksi(Request $request)
   {
      // dd($request->all());
      $tanggalAwal  = $request->tanggal_awal;
      $tanggalAkhir = $request->tanggal_akhir;
      $filterTrans  = $request->filter_transaksi;

      // VALIDASI LOGIKA TANGGAL
      if ($tanggalAwal && $tanggalAkhir && $tanggalAwal > $tanggalAkhir) {
         return response()->json([
            'success' => false,
            'msg' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir'
         ], 422);
      }

      // BASE QUERY
      $query = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->select('transaksis.id as id_transaksi', 'transaksis.*', 'users.name')
         ->where('tenant_id', $this->getTetantId());

      if ($tanggalAwal && $tanggalAkhir) {
         $query->whereBetween('transaksis.created_at', [
            Carbon::parse($tanggalAwal)->startOfDay(),
            Carbon::parse($tanggalAkhir)->endOfDay()
         ]);
      }

      // TRANSAKSI TERTINGGI / TERENDAH
      if ($filterTrans == 'total_up') {
         $query->orderBy('total_harga', 'DESC');
      } elseif ($filterTrans == 'total_min') {
         $query->orderBy('total_harga', 'ASC');
      }

      $transaksi = $query->get();
      // dd($transaksi);

      return response()->json([
         'success' => 'true',
         'inputan' => [
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'total_harga_filter' => $filterTrans
         ],
         'transaksi' => $transaksi,
      ]);
   }

   public function exportTransaksiExcel(Request $request)
   {
      $tanggalAwal  = $request->tanggal_awal;
      $tanggalAkhir = $request->tanggal_akhir;
      $filterTrans  = $request->filter_transaksi;

      // ================= QUERY (SAMA DENGAN FILTER) =================
      $query = Transaksi::join('users', 'users.id', '=', 'transaksis.kasir_id')
         ->select(
            'transaksis.kode_transaksi',
            'users.name as kasir',
            'transaksis.nama_pelanggan',
            'transaksis.total_item',
            'transaksis.total_harga',
            'transaksis.bayar',
            'transaksis.kembalian',
            'transaksis.metode_pembayaran',
            'transaksis.status',
            'transaksis.created_at'
         )
         ->where('tenant_id', $this->getTetantId());

      if ($tanggalAwal && $tanggalAkhir) {
         $query->whereBetween('transaksis.created_at', [$tanggalAwal, $tanggalAkhir]);
      }

      if ($filterTrans === 'total_up') {
         $query->orderBy('transaksis.total_harga', 'DESC');
      } elseif ($filterTrans === 'total_min') {
         $query->orderBy('transaksis.total_harga', 'ASC');
      }

      $transaksi = $query->get();

      // ================= BUAT EXCEL =================
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      // 🔹 JUDUL
      $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI PENJUALAN');
      $sheet->mergeCells('A1:K1');
      $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

      // 🔹 PERIODE
      $sheet->setCellValue(
         'A2',
         "Periode : {$tanggalAwal} - {$tanggalAkhir}"
      );
      $sheet->mergeCells('A2:K2');
      $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      // 🔹 HEADER (ROW 4)
      $headerRow = 4;
      $headers = [
         'No',
         'Kode Transaksi',
         'Kasir',
         'Nama Pelanggan',
         'Total Item',
         'Total Harga',
         'Bayar',
         'Kembalian',
         'Pembayaran',
         'Status',
         'Tanggal'
      ];

      $col = 'A';
      foreach ($headers as $header) {
         $sheet->setCellValue($col . $headerRow, $header);
         $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
         $sheet->getStyle($col . $headerRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
         $col++;
      }

      // 🔹 DATA
      $row = 5;
      $no = 1;

      foreach ($transaksi as $trx) {
         $sheet->fromArray([
            $no++,
            $trx->kode_transaksi,
            $trx->kasir,
            $trx->nama_pelanggan,
            $trx->total_item,
            'Rp ' . number_format($trx->total_harga, 0, ',', '.'),
            'Rp ' . number_format($trx->bayar, 0, ',', '.'),
            'Rp ' . number_format($trx->kembalian, 0, ',', '.'),
            $trx->metode_pembayaran,
            $trx->status,
            $trx->created_at->format('d/m/Y'),
         ], null, 'A' . $row);

         // tinggi baris NORMAL (biar tidak tinggi)
         $sheet->getRowDimension($row)->setRowHeight(18);
         $row++;
      }

      // 🔹 BORDER TABLE
      $sheet->getStyle("A4:K" . ($row - 1))
         ->getBorders()
         ->getAllBorders()
         ->setBorderStyle(Border::BORDER_THIN);

      // 🔹 LEBAR KOLOM
      foreach (range('A', 'K') as $columnID) {
         $sheet->getColumnDimension($columnID)->setAutoSize(true);
      }

      // ================= DOWNLOAD =================
      $filename = 'Laporan_Transaksi' . $tanggalAwal . '-' . $tanggalAkhir . '.xlsx';
      $writer = new Xlsx($spreadsheet);

      return response()->streamDownload(function () use ($writer) {
         $writer->save('php://output');
      }, $filename);
   }

   public function filterTransaksiProduk(Request $request)
   {
      $filterProduk = $request->filter_produk; // transaksi_up / transaksi_down
      $startDate    = $request->start_date;    // YYYY-MM-DD
      $endDate      = $request->end_date;      // YYYY-MM-DD

      $produk = null;

      if ($filterProduk) {

         $produk = DetailTransaksi::join('transaksis', 'transaksis.id', '=', 'detail_transaksis.transaksi_id')
            ->join('produk_toko', 'produk_toko.id', '=', 'detail_transaksis.produk_toko_id')
            ->join('produk', 'produk.id', '=', 'produk_toko.produk_id')
            ->select(
               'produk.id as produk_id',
               'produk.nama_produk',
               'produk_toko.harga_jual',
               DB::raw('SUM(detail_transaksis.jumlah) as total_jual'),
               DB::raw('SUM(detail_transaksis.total_harga_produk) as total_penjualan')
            )
            ->where('transaksis.tenant_id', $this->getTetantId());

         /*
      |--------------------------------------------------
      | FILTER RENTANG TANGGAL (INI YANG KURANG)
      |--------------------------------------------------
      */
         if ($startDate && $endDate) {
            $produk->whereBetween(
               DB::raw('DATE(transaksis.created_at)'),
               [$startDate, $endDate]
            );
         }

         $produk = $produk
            ->groupBy(
               'produk.id',
               'produk.nama_produk',
               'produk_toko.harga_jual'
            )
            ->orderBy(
               'total_jual',
               $filterProduk === 'transaksi_up' ? 'DESC' : 'ASC'
            )
            ->get();
      }

      // =========================
      // EXPORT EXCEL
      // =========================
      if ($request->input('print') == 1) {

         $titleSearch = $filterProduk === 'transaksi_up'
            ? 'Tinggi Penjualan'
            : 'Minim Penjualan';

         $periode = ($startDate && $endDate)
            ? "{$startDate} s/d {$endDate}"
            : 'Semua Periode';

         $spreadsheet = new Spreadsheet();
         $sheet = $spreadsheet->getActiveSheet();

         // 🔹 JUDUL
         $sheet->setCellValue('A1', 'LAPORAN TOTAL TRANSAKSI PRODUK');
         $sheet->mergeCells('A1:D1');
         $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

         // 🔹 SUB JUDUL
         $sheet->setCellValue('A2', "Berdasarkan Penjualan : {$titleSearch}");
         $sheet->mergeCells('A2:D2');
         $sheet->setCellValue('A3', "Periode : {$periode}");
         $sheet->mergeCells('A3:D3');

         // 🔹 HEADER
         $headers = ['No', 'Nama Produk', 'Harga Produk', 'Total Terjual'];
         $rowHeader = 5;

         $col = 'A';
         foreach ($headers as $header) {
            $sheet->setCellValue($col . $rowHeader, $header);
            $sheet->getStyle($col . $rowHeader)->getFont()->setBold(true);
            $col++;
         }

         // 🔹 DATA
         $row = 6;
         $no = 1;
         foreach ($produk as $rows) {
            $sheet->fromArray([
               $no++,
               $rows->nama_produk,
               'Rp ' . number_format($rows->harga_jual, 0, ',', '.'),
               $rows->total_jual,
            ], null, 'A' . $row);
            $row++;
         }

         $sheet->getStyle("A5:D" . ($row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

         foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
         }

         $filename = 'Laporan_produk_' . str_replace(' ', '_', $titleSearch) . '.xlsx';
         $writer = new Xlsx($spreadsheet);

         return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
         }, $filename);
      }

      // =========================
      // RESPONSE JSON
      // =========================
      return response()->json([
         'inputan' => [
            'filter_produk' => $filterProduk,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
         ],
         'data_produk_transaksi' => $produk
      ]);
   }
}
