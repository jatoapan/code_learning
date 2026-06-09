<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Already protected by route middleware
    }

    public function rules()
    {
        return [
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,public,unlisted',
            'has_leaderboard' => 'boolean',
        ];
    }
}
