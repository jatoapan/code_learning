<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualEnrollRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|uuid',
            'role'    => 'required|string|in:student'
        ];
    }
}
