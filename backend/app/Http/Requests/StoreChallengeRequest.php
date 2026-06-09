<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallengeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'language_id' => 'required|integer',
            'language_name' => 'required|string',
            'points' => 'required|integer|min:0',
            'starter_code' => 'nullable|string',
        ];
    }
}
