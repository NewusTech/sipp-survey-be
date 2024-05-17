<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPerkerasan extends Model
{
    use SoftDeletes;
    protected $table = 'jenis_perkerasan';
    protected $fillable = [
        'ruas_jalan_id',
        'rigit',
        'hotmix',
        'lapen',
        'telford',
        'agregat',
        'onderlagh',
        'tanah',
        'created_by',
        'tahun',
        'baik',
        'sedang',
        'rusak_ringan',
        'rusak_berat',
        'lhr',
        'keterangan'
    ];
}
