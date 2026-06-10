<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email:rfc,dns|max:255|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim($this->email))]);
    }
}
