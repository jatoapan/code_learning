<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlashcardDeckRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
