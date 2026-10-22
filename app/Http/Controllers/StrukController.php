<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class StrukController extends Controller
{
    //
    public function cekStruk($id)
    {
        $getTransaksi = Transaksi::join('users', 'transaksis.kasir_id', '=', 'users.id')
            ->select('transaksis.*', 'users.name as nama_kasir')
            ->where('transaksis.id', $id)
            ->first();

        $detailTransaksi = DetailTransaksi::join('produk', 'detail_transaksis.produk_toko_id', '=', 'produk.id')
            ->select('detail_transaksis.*', 'produk.nama_produk')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();

        // dd($getTransaksi, $detailTransaksi);
        $data  = [
            'data_transaksi' => $getTransaksi,
            'detail_transaksi' => $detailTransaksi,
        ];

        return view('pages.struk_transaksi', $data);
    }
}
