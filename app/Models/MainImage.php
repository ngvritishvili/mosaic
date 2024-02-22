<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainImage extends Model
{
    use HasFactory;

    protected $fillable  = [
        'position_x',
        'position_y',
        'resolution',
        'dark_range',
        'medium_range',
        'light_range',
        'filename',
        'path',
    ];

    protected $table = 'temporary_main_pieces';


    public function params()
    {
        return $this->hasOne(\DB::raw('image_params'), 'main_id');
    }
}
