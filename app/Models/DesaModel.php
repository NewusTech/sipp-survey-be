<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaModel extends Model
{
    protected $table = 'master_desa';
    protected $fillable = [
        'kecamatan_id',
        'nama'
    ];
}
