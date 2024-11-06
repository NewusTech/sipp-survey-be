<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyDrainaseModel extends Model
{
    use SoftDeletes;
    protected $table = 'survey_drainase';
    protected $fillable = [
        'ruas_drainase_id',
        'panjang_drainase',
        'letak_drainase',
        'lebar_atas',
        'lebar_bawah',
        'tinggi',
        'kondisi',
        'status',
        'keterangan',
        'latitude',
        'longitude'
    ];
}
