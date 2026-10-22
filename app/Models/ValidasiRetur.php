<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidasiRetur extends Model
{
    use HasFactory;

    protected $table = 'validasi_retur';

    protected $fillable = [
        'transaksi_id',
        'detail_transaksi_id',
        'admin_id',
        'jumlah_retur',
        'status_validasi',
        'keterangan',
    ];
}
