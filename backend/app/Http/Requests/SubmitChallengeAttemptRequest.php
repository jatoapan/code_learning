<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitChallengeAttemptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'submitted_code' => 'required|string|max:65535', // Max 64KB
            'language_id'    => 'required|integer',
        ];
    }
}
