<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jembatan extends Model
{
    use SoftDeletes;
    protected $table = 'jembatan';
    protected $fillable = [
        'no_ruas',
        'kecamatan_id',
        'nama_ruas',
        'no_jembatan',
        'asal',
        'nama_jembatan',
        'kmpost',
        'panjang',
        'lebar',
        'jml_bentang',
        'tipe_ba',
        'kondisi_ba',
        'tipe_bb',
        'kondisi_bb',
        'tipe_fondasi',
        'kondisi_fondasi',
        'bahan',
        'kondisi_lantai',
        'latitude',
        'longitude',
        'status',
        'keterangan',
        'created_by',
        'tahun'
    ];
}
