<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'   => 'sometimes|string|max:255',
            'avatar' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048', // máx 2MB
        ];
    }
}
