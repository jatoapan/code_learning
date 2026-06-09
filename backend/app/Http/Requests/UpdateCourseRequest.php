<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest {
    public function authorize() { return true; }
    
    public function rules() {
        return [
            'category' => 'sometimes|required|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image_path' => 'nullable|string|max:255',
            'status' => 'sometimes|required|string|in:draft,public,unlisted',
            'has_leaderboard' => 'boolean',
        ];
    }
}
