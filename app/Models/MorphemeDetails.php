<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MorphemeDetails extends Model
{
    use HasFactory;

    protected $casts = [
        'sheet_words' => 'array'
    ];
}
