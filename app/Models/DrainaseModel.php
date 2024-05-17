<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrainaseModel extends Model
{
    use SoftDeletes;
    protected $table = 'drainase';
    protected $fillable = [
        'nama_ruas',
        'panjang_ruas',
        'desa_id'
    ];
}
