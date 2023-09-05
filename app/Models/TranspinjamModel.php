<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class TranspinjamModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'no_pinjam', 
        'id_anggota',
        'tanggal_pinjam',
        'tanggal_perpanjang',
        'tanggal_kembali',
        'status',
        'create_by',
        'update_by'
    ];
    protected $table = 'master_transaksi_pinjam';

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }
}
