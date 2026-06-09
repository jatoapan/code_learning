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
            'submitted_code' => 'required|string',
            'language_id' => 'required|integer',
        ];
    }
}
