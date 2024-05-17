<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KondisiPerkerasan extends Model
{
    use SoftDeletes;
    protected $table = 'kondisi_perkerasan';
    protected $fillable = [
        'ruas_jalan_id',
        'baik',
        'sedang',
        'rusak_ringan',
        'rusak_berat',
        'created_by'
    ];
}
