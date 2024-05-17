<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyDrainasePhoto extends Model
{
    use SoftDeletes;
    protected $table = 'survey_drainase_photo';
    protected $fillable = [
        'desa_id',
        'photo'
    ];
}
