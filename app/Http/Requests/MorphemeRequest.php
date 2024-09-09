<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MorphemeRequest extends FormRequest
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'rootWord' => 'required|string'
        ];
    }
}
