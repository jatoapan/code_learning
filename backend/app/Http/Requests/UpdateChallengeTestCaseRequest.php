<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChallengeTestCaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'input' => 'nullable|string',
            'expected_output' => 'required|string',
            'is_hidden' => 'boolean',
        ];
    }
}
