<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagesLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'path',
        'category_id',
        'dark_range',
        'medium_range',
        'light_range',
    ];
}
