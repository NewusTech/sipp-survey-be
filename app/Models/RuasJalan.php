<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuasJalan extends Model
{
    use SoftDeletes;
    protected $table = 'master_ruas_jalan';
    protected $fillable = [
        'no_ruas',
        'nama',
        'koridor_id',
        'panjang_ruas',
        'akses',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'desa',
        'latitude',
        'longitude',
        'status',
        'alasan',
        'created_by',
        'lebar'
    ];
}
