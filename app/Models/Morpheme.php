<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Morpheme extends Model
{
    use HasFactory;

    protected $casts = [
        'matching_words' => 'array',
        'sheet_reference' => 'array'
    ];

    public function morphemesDetail(){
        return $this->hasMany(MorphemeDetails::class, 'root_word_id','root_word_id');
    }
}
