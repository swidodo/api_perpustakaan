<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class LogpinjamModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'id_transaksi_pinjam', 
        'no_pinjam', 
        'id_anggota',
        'tanggal_pinjam',
        'tanggal_perpanjang',
        'tanggal_kembali',
        'status',
        'id_buku',
        'jumlah',
        'create_by',
        'update_by',
        'trans_type',
        'created_at'
    ];

    protected $table = 'log_transaksi_pinjam';
}
