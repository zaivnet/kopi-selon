<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Produk;
use App\Models\Tenant;
use App\Models\Validasi;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\ProdukToko;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use App\Models\ValidasiRetur;
use App\Models\StokTokoKeluar;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
   //
   public function tenantId()
   {
      $tenantID = session('active_tenant_id');
      $findTenant = Tenant::where('id', $tenantID)->first();
      return $findTenant->id;
   }

   public function postTransaksi(Request $request)
   {
      $this->validate($request, [
         'bayar' => 'required',
         'kembalian' => 'required',
         'total_harga' => 'required',
         'metode_pembayaran' => 'required',
         'kode_transaksi' => 'required',
         'produk_varian_id' => 'required|array',
         'jumlah' => 'required|array',
         'harga_satuan' => 'required|array',
         'total_harga_produk' => 'required|array',
      ]);

      $detail_transaksi = [];
      $poinPelanggan = 'Gabung jadi member untuk mendapatkan poin dari setiap kelipatan pembelanjaan Rp10.000 akan mendapatkan 1 poin.';
      $produk_varian_id = $request->input('produk_varian_id');

      $total_item = 0;
      $total_harga = 0;
      for ($i = 0; $i < count($produk_varian_id); $i++) {

         $produk = ProdukVarian::join('produk_toko', 'produk_toko.id', '=', 'produk_varian.produk_toko_id')
            ->select('produk_varian.nama as nama_produk', 'produk_toko.stok', 'produk_toko.id as produk_toko_id', 'produk_varian.id as produk_varian_id')
            ->where('produk_varian.id', $produk_varian_id[$i])
            ->first();

         if ($produk->stok < $request->input('jumlah')[$i]) {
            return response()->json([
               'status' => 'error',
               'msg' => 'Stok produk ' . $produk->nama_produk . ' tidak mencukupi. Stok tersedia : ' . $produk->stok,
            ]);
         }

         $harga_satuan = str_replace([',', '.'], '', $request->input('harga_satuan')[$i]);
         $total_harga_produk  = $harga_satuan * $request->input('jumlah')[$i];
         $detail_transaksi[] = [
            'produk_toko_id' => $produk->produk_toko_id,
            'produk_varian_id' => $produk->produk_varian_id,
            'nama_produk' => $produk->nama_produk,
            'jumlah' => $request->input('jumlah')[$i],
            'harga_satuan' => $harga_satuan,
            'total_harga_produk' => $total_harga_produk,
         ];
         $total_item += $request->input('jumlah')[$i];
         $total_harga += $total_harga_produk;
         // Contoh: Transaksi::create([...]);
      }

      $poin = intdiv($total_harga, 10000);

      $data_transaksi = [
         'tenant_id' => $this->tenantId(),
         'kode_transaksi' => $request->input('kode_transaksi'),
         'kasir_id' => auth()->user()->id,
         'pelanggan_id' => $request->input('id_pelanggan'),
         'nama_pelanggan' => $request->input('nama_pelanggan'),
         'total_item' => $total_item,
         'total_harga' => $total_harga,
         'bayar' => str_replace([',', '.'], '', $request->input('bayar')),
         'kembalian' => str_replace([',', '.'], '', $request->input('kembalian')),
         'metode_pembayaran' => $request->input('metode_pembayaran'),
         'status' => 'selesai',
      ];

      // return response()->json([
      //    'status' => 'testing',
      //    'data' => [
      //       'data_transaksi' => $data_transaksi,
      //       'detail_transaksi' => $detail_transaksi,
      //    ]
      // ]);

      // return;
      // dd($data_transaksi, $detail_transaksi);

      if ($request->input('id_pelanggan') != null) {
         // dd("Poin pelanggan " . $request->input('nama_pelanggan') . ' Bertambah ' . $poin);
         $cekPelanggan = DB::table('pelanggans')->where('id', $request->input('id_pelanggan'))->exists();
         if ($cekPelanggan) {
            // dd('poin pelanggan di update');
            $pelanggan = Pelanggan::findOrFail($request->input('id_pelanggan'));
            $poinSebelumnya = $pelanggan->poin;
            // dd($pelanggan);
            $pelanggan->update([
               'poin' => $pelanggan->poin + $poin
            ]);

            if ($poinSebelumnya < $pelanggan->poin) {
               $poinPelanggan = 'Poin pelanggan ' . $pelanggan->nama . ' bertambah ' . $poin . ' poin. Total poin sekarang ' . $pelanggan->poin . ' poin.';
            }
         }
      }


      $crreateTransaksi = Transaksi::create($data_transaksi);

      foreach ($detail_transaksi as $detail) {
         DetailTransaksi::create([
            'transaksi_id' => $crreateTransaksi->id,
            'produk_varian_id' => $detail['produk_varian_id'],
            'jumlah' => $detail['jumlah'],
            'harga_satuan' => $detail['harga_satuan'],
            'total_harga_produk' => $detail['total_harga_produk'],
         ]);

         $produk_toko = ProdukToko::FindOrFail($detail['produk_toko_id']);
         $produk_toko->update([
            'stok' => $produk_toko->stok - $detail['jumlah']
         ]);
      }

      return response()->json([
         'status' => 'success',
         'msg' => 'Transaksi berhasil diproses',
         'data' => [
            'transaksi' => $data_transaksi,
            'detail_transaksi' => $detail_transaksi,
            'nama_kasir' => auth()->user()->name,
            'poin_pelanggan' => $poinPelanggan,
            'tanggal_transaksi' => Carbon::parse(now())->locale('id')->translatedFormat('d F Y H:i'),
         ],
         'kode_transaksi_baru' => 'TRX' . date('YmdHis')
      ]);
      // return redirect()->route('kasir.transaksi')->with('success', 'Transaksi berhasil disimpan.');
   }

   private function center($text, $width = 32)
   {
      $text = trim($text);
      if (strlen($text) >= $width) return substr($text, 0, $width) . "\n";

      $pad = intdiv($width - strlen($text), 2);
      return str_repeat(' ', $pad) . $text . "\n";
   }

   private function line($width = 32)
   {
      return str_repeat('-', $width) . "\n";
   }

   private function wrapText($text, $width = 32)
   {
      return explode("\n", wordwrap($text, $width, "\n", true));
   }

   private function lr($left, $right, $width = 32)
   {
      $left  = substr($left, 0, $width);
      $right = substr($right, 0, $width);

      $space = $width - strlen($left) - strlen($right);
      if ($space < 1) $space = 1;

      return $left . str_repeat(' ', $space) . $right . "\n";
   }


   public function strukBase64($transaksiId)
   {
      $tenantId = $this->tenantId();

      /* ================= HEADER TRANSAKSI ================= */
      $transaksi = Transaksi::where('tenant_id', $tenantId)
         ->where('kode_transaksi', $transaksiId)
         ->first();

      $kasir = User::where('id', $transaksi->kasir_id)
         ->select('users.id', 'users.name')
         ->first();

      $pelanggan = Pelanggan::where('id', $transaksi->pelanggan_id)->first();

      /* ================= DETAIL TRANSAKSI ================= */
      $detailTransaksi = DetailTransaksi::join(
         'produk_varian',
         'produk_varian.id',
         '=',
         'detail_transaksis.produk_varian_id'
      )
         ->where('detail_transaksis.transaksi_id', $transaksi->id)
         ->select(
            'produk_varian.nama as nama_produk',
            'detail_transaksis.jumlah',
            'detail_transaksis.total_harga_produk'
         )
         ->get();

      /* ================= TENANT ================= */
      $tenant = Tenant::select('name', 'address', 'tiktok', 'instagram')
         ->where('id', $tenantId)
         ->first();

      /* ================= FORMAT DATA ================= */
      $tanggalTransaksi = Carbon::parse($transaksi->created_at)
         ->locale('id')
         ->translatedFormat('d F Y H:i');

      $namaKasir = $kasir->name ?? '-';

      $metode = $transaksi->metode_pembayaran === 'offline'
         ? 'Tunai'
         : 'QRIS';

      /* ================= POIN (STRING) ================= */
      $poin = intdiv($transaksi->total_harga, 10000);
      $poinPelanggan = $transaksi->pelanggan_id
         ? "Poin anda bertambah {$poin} poin,total poin sekarang {$pelanggan->poin} poin"
         : 'Gabung member untuk dapatkan poin yang bisa ditukarkan dengan hadiah menarik';

      /* ================= BUILD STRUK ================= */
      $struk  = "\n";
      $struk .= $this->center(strtoupper($tenant->name));
      foreach ($this->wrapText($tenant->address) as $line) {
         $struk .= $this->center($line);
      }
      $struk .= $this->line();

      $struk .= $this->lr('No. Struk', $transaksi->kode_transaksi);
      $struk .= $this->lr('Tanggal', $tanggalTransaksi);
      $struk .= $this->lr('Kasir', $namaKasir);

      $struk .= $this->line();

      foreach ($detailTransaksi as $item) {
         $struk .= substr($item->nama_produk, 0, 32) . "\n";
         $struk .= $this->lr(
            '  x' . $item->jumlah,
            'Rp' . number_format($item->total_harga_produk, 0, ',', '.')
         );
      }

      $struk .= $this->line();

      $struk .= $this->lr('TOTAL', 'Rp' . number_format($transaksi->total_harga, 0, ',', '.'));
      $struk .= $this->lr('BAYAR', 'Rp' . number_format($transaksi->bayar, 0, ',', '.'));
      $struk .= $this->lr('KEMBALI', 'Rp' . number_format($transaksi->kembalian, 0, ',', '.'));
      $struk .= $this->lr('Metode', $metode);

      if ($transaksi->pelanggan_id) {
         $struk .= $this->lr('Pelanggan', $transaksi->nama_pelanggan);
      } else {
         $struk .= $this->lr('Pelanggan', '-');
      }

      $struk .= $this->line();
      foreach ($this->wrapText($poinPelanggan) as $line) {
         $struk .= $this->center($line);
      }
      $struk .= $this->line();

      $struk .= $this->center('Terima kasih sudah berkunjung!');
      $struk .= $this->center('TikTok : ' . $tenant->tiktok);
      $struk .= $this->center('Instagram : ' . $tenant->instagram);

      // $struk .= "\n";

      return response($struk)
         ->header('Content-Type', 'text/plain; charset=ASCII');
   }


   // public function updateProdukTransaksi(Request $request, $id)
   // {
   //    dd($request->all());
   //    $status = $request->input('status');
   //    $prosessRetur = false;

   //    // dd($status);
   //    $detail_transaksi = DetailTransaksi::findOrFail($id);

   //    if ($detail_transaksi->jumlah == $request->jumlah) {
   //       return back()->with('error', 'Tidak ada jumlah produk yang dikurangi..');
   //    }
   //    $validasi_retur = ValidasiRetur::where('detail_transaksi_id', $id)->first();

   //    if ($status == 'pembatalan') {

   //       if ($validasi_retur != null) {
   //          // dd('gagal sebelumnya memiliki data retur');
   //          return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena sudah ada request retur yang sedang di proses.');
   //       } else {
   //          $prosessRetur = true;
   //       }

   //       // dd('pembatalan produk');
   //    }
   //    // dd($prosessRetur);

   //    if ($status == 'retur') {
   //       // dd($validasi_retur);
   //       if ($validasi_retur != null) {
   //          if ($validasi_retur->status_validasi == 'rejected') {
   //             return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena request retur telah ditolak oleh admin.');
   //          }
   //          if ($validasi_retur->status_validasi == 'done') {
   //             return back()->with('error', 'Request retur hanya bisa di lakukan satu kali.');
   //          }
   //          if ($validasi_retur->status_validasi == 'pending') {
   //             return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena request retur belum di proses');
   //          } elseif ($validasi_retur->status_validasi == 'approved') {
   //             // dd('retur sudah di proses, update jumlah produk di detail transaksi');
   //             $prosessRetur = true;
   //          }
   //       } else {
   //          // dd('buat request retur dulu');
   //          ValidasiRetur::create([
   //             'detail_transaksi_id' => $detail_transaksi->id,
   //             'admin_id' => 0,
   //             'transaksi_id' => $detail_transaksi->transaksi_id,
   //             'jumlah_retur' => $request->jumlah_retur,
   //             'status_validasi' => 'pending',
   //             'keterangan' => ($request->keterangan == '' ? 'Proses retur produk' : $request->keterangan),
   //          ]);
   //          return back()->with('success', 'Request retur produk berhasil dikirim untuk di proses.');
   //       }
   //    }

   //    if ($prosessRetur == true) {
   //       // dd('prosess retur true');
   //       $transaksi = Transaksi::findOrFail($detail_transaksi->transaksi_id);
   //       $detail_transaksi_all = DetailTransaksi::where('transaksi_id', $transaksi->id)->get();
   //       $detail_transaksi_all_update = [];
   //       $total_transaksi_update = 0;
   //       $total_item_update = 0;
   //       $jumlah_retur = $request->jumlah_retur;
   //       foreach ($detail_transaksi_all as $row) {
   //          $jumlah = 0;
   //          $total_harga_produk = 0;

   //          if ($row->id == $detail_transaksi->id) {
   //             $jumlah = $detail_transaksi->jumlah - $request->jumlah_retur;
   //          } else {
   //             $jumlah = $row->jumlah;
   //          }

   //          $total_item_update += $jumlah;
   //          $total_harga_produk = $row->harga_satuan * $jumlah;

   //          DetailTransaksi::Where('id', $row->id)->update([
   //             'jumlah' => $jumlah,
   //             'total_harga_produk' => $total_harga_produk
   //          ]);

   //          $total_transaksi_update += $total_harga_produk;
   //          $detail_transaksi_all_update[] = [
   //             'id' => $row->id,
   //             'produk_varian_id' => $row->produk_varian_id,
   //             'jumlah' => $jumlah,
   //             'harga_satuan' => $row->harga_satuan,
   //             'total_harga_produk' => $total_harga_produk,
   //          ];
   //       }
   //       // dd($total_harga_produk);

   //       // $jumlah_retur = $transaksi->total_item - $total_item_update;

   //       $transaksi->update([
   //          'total_item' => $total_item_update,
   //          'total_harga' => $total_transaksi_update,
   //          'bayar' => $total_transaksi_update,
   //          'kembalian' => 0
   //       ]);

   //       StokTokoKeluar::create([
   //          'tenant_id' => $this->tenantId(),
   //          'produk_varian_id' => $detail_transaksi->produk_varian_id,
   //          'kasir_id' => auth()->user()->id,
   //          'jumlah_retur' => $jumlah_retur,
   //          'harga_produk' => $detail_transaksi->harga_satuan,
   //          'keterangan' => $status . ' produk pada transaksi ' . $transaksi->kode_transaksi
   //       ]);

   //       if ($status == 'pembatalan') {
   //          $produkVarian = ProdukVarian::findOrFail($row->produk_varian_id);
   //          ProdukToko::where('id', $produkVarian->produk_toko_id)
   //             ->update([
   //                'stok' => DB::raw('stok + ' . $row->jumlah)
   //             ]);
   //       }

   //       if ($status == 'retur') {
   //          ValidasiRetur::where('detail_transaksi_id', $detail_transaksi->id)
   //             ->update([
   //                'status_validasi' => 'done',
   //             ]);
   //       }

   //       // dd(Pelanggan::where('id', $transaksi->pelanggan_id)->first());

   //       if ($transaksi->pelanggan_id && $jumlah_retur > 0) {

   //          $nilaiRetur = $detail_transaksi->harga_satuan * $jumlah_retur;
   //          $poinRetur  = intdiv($nilaiRetur, 10000);

   //          if ($poinRetur > 0) {
   //             Pelanggan::where('id', $transaksi->pelanggan_id)
   //                ->update([
   //                   'poin' => DB::raw("GREATEST(poin - {$poinRetur}, 0)")
   //                ]);
   //          }
   //       }


   //       // dd($transaksi, $detail_transaksi_all, $detail_transaksi_all_update, 'Total item update : ' . $total_item_update, 'Total transaksi di update : ' . $total_transaksi_update, 'Jumlah Retur Produk : ' . $jumlah_retur);
   //       return back()->with('success', 'Detail transaksi berhasil di update dan transaksi berhasil di sinkronisasi.');
   //    }

   //    // dd('retur sudah di proses, update jumlah produk di detail transaksi');
   // }

   public function updateProdukTransaksi(Request $request, $id)
   {
      // dd(auth()->user()->name);
      // =========================
      // 1️⃣ VALIDASI REQUEST
      // =========================
      $request->validate(
         [
            'type'          => 'required|in:pembatalan,retur',
            'jumlah_retur'  => 'required|integer|min:1',
            'keterangan'    => 'nullable|string',
         ],
         $message = [
            'type.required'            => 'Jenis request wajib diisi.',
            'type.in'                  => 'Jenis request tidak valid.',
            'jumlah_retur.required'    => 'jumlah item tidak berkurang, proses dibatalkan.',
            'jumlah_retur.min'         => 'jumlah item tidak berkurang, proses dibatalkan.'
         ]
      );

      // =========================
      // 2️⃣ JUMLAH RETUR = 0 → STOP
      // =========================
      if ((int) $request->jumlah_retur === 0) {
         return back()->with('error', 'Jumlah pembatalan / retur tidak boleh 0.');
      }

      $detail = DetailTransaksi::findOrFail($id);

      // =========================
      // 3️⃣ JUMLAH RETUR TIDAK BOLEH MELEBIHI JUMLAH ITEM
      // =========================
      if ($request->jumlah_retur > $detail->jumlah) {
         return back()->with('error', 'Jumlah retur melebihi jumlah produk di transaksi.');
      }

      // =========================
      // 4️⃣ CEK REQUEST SEBELUMNYA (1x SAJA)
      // =========================
      $cekValidasi = Validasi::where('detail_transaksi_id', $detail->id)->first();

      if ($cekValidasi) {
         return back()->with(
            'error',
            'Produk ini sudah memiliki request pembatalan / retur dan sedang diproses.'
         );
      }

      $hargaItem = $request->jumlah_retur * $detail->harga_satuan;
      // dd($hargaItem);

      // =========================
      // 5️⃣ SIMPAN REQUEST VALIDASI
      // =========================
      Validasi::create([
         'transaksi_id'        => $detail->transaksi_id,
         'detail_transaksi_id' => $detail->id,
         'admin_id'            => null,
         'type'                => $request->type,
         'jumlah_item'         => $request->jumlah_retur,
         'harga_item'          => $hargaItem,
         'status_validasi'     => 'pending',
         'keterangan'          => $request->keterangan
            ?: 'Request ' . $request->type . ' oleh ' . auth()->user()->name,
      ]);

      return back()->with(
         'success',
         'Request ' . $request->type . ' berhasil dikirim dan menunggu persetujuan admin.'
      );
   }


   public function hapusProdukTransaksi(Request $request, $id)
   {
      $status = $request->input('status');
      $prosessRetur = false;
      // if ($validasi_retur) {
      //    return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di hapus karena sudah divalidasi retur.');
      // }
      $detail_transaksi = DetailTransaksi::findOrFail($id);

      $transaksi = Transaksi::findOrFail($detail_transaksi->transaksi_id);
      $detail_transaksi_all = DetailTransaksi::where('transaksi_id', $transaksi->id)->get();
      $validasi_retur = ValidasiRetur::where('detail_transaksi_id', $id)->first();


      if ($status == 'pembatalan') {

         if ($validasi_retur != null) {
            // dd('gagal sebelumnya memiliki data retur');
            return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena sudah ada request retur yang sedang di proses.');
         } else {
            $prosessRetur = true;
         }

         // dd('pembatalan produk');
      }

      if ($status == 'retur') {
         // dd($validasi_retur);
         if ($validasi_retur != null) {
            if ($validasi_retur->status_validasi == 'rejected') {
               return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena request retur telah ditolak oleh admin.');
            }
            if ($validasi_retur->status_validasi == 'done') {
               return back()->with('error', 'Request retur hanya bisa di lakukan satu kali.');
            }
            if ($validasi_retur->status_validasi == 'pending') {
               return back()->with('error', 'Produk pada detail transaksi ini tidak dapat di ubah karena request retur belum di proses');
            } elseif ($validasi_retur->status_validasi == 'approved') {
               // dd('retur sudah di proses, update jumlah produk di detail transaksi');
               $prosessRetur = true;
            }
         } else {
            // dd('buat request retur dulu');
            ValidasiRetur::create([
               'detail_transaksi_id' => $detail_transaksi->id,
               'admin_id' => 0,
               'transaksi_id' => $detail_transaksi->transaksi_id,
               'jumlah_retur' => $request->jumlah_retur,
               'status_validasi' => 'pending',
               'keterangan' => ($request->keterangan == '' ? 'Proses retur produk' : $request->keterangan),
            ]);
            return back()->with('success', 'Request retur produk berhasil dikirim untuk di proses.');
         }
      }

      if ($prosessRetur == true) {
         // dd('proses retur berjalan');
         // dd($transaksi->pelanggan_id, $detail_transaksi_all);
         $poin = intdiv($detail_transaksi->total_harga_produk, 10000);

         $total_transaksi_update = 0;
         $total_item_update = 0;

         foreach ($detail_transaksi_all as $row) {
            if ($row->id == $detail_transaksi->id) {
               // dd('hapus produk dari detail transaksi');
               StokTokoKeluar::create([
                  'tenant_id' => $this->tenantId(),
                  'produk_varian_id' => $row->produk_varian_id,
                  'kasir_id' => auth()->user()->id,
                  'jumlah_retur' => $row->jumlah,
                  'harga_produk' => $row->harga_satuan,
                  'keterangan' => $status . ' produk pada transaksi ' . $transaksi->kode_transaksi
               ]);
               if ($status == 'pembatalan') {
                  // dd('pembatalan produk');
                  $produkVarian = ProdukVarian::findOrFail($row->produk_varian_id);
                  ProdukToko::where('id', $produkVarian->produk_toko_id)
                     ->update([
                        'stok' => DB::raw('stok + ' . $row->jumlah)
                     ]);
               }
               $detail_transaksi->delete();
            } else {
               // dd('produk tetap di detail transaksi');
               $total_item_update += $row->jumlah;
               $total_transaksi_update += $row->total_harga_produk;
            }
         }

         // dd($total_item_update, $total_transaksi_update);
         // dd($detail_transaksi, $transaksi, $detail_transaksi_all);
         $transaksi->update([
            'total_item' => $total_item_update,
            'total_harga' => $total_transaksi_update,
            'bayar' => $total_transaksi_update,
            'kembalian' => 0
         ]);

         if ($poin > 0 && $transaksi->pelanggan_id) {
            Pelanggan::where('id', $transaksi->pelanggan_id)
               ->decrement('poin', $poin);
         }

         return back()->with('success', 'Detail transaksi berhasil di update dan transaksi berhasil di sinkronisasi.');
      }
   }
}
