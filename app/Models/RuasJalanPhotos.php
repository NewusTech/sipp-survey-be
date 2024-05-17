<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuasJalanPhotos extends Model
{
    use SoftDeletes;
    protected $table = 'ruas_jalan_photos';
    protected $fillable = [
        'ruas_jalan_id',
        'image'
    ];
}
