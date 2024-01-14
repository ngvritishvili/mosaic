<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canvas extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'resolution',
        'path',
    ];
}
