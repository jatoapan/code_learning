<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChallengeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'string|max:255',
            'description' => 'string',
            'difficulty' => 'string|in:easy,medium,hard',
            'points' => 'integer|min:0',
        ];
    }
}
