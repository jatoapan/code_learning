<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddStaffRequest extends FormRequest {
    public function authorize() { return true; }
    
    public function rules() {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'required|string|in:professor,ta',
        ];
    }
}
