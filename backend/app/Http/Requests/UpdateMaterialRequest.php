<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:pdf,video_link,ppt,pptx,video',
            'content' => 'sometimes|required|string',
            'estimated_minutes' => 'sometimes|required|integer|min:1',
            'is_required' => 'boolean',
        ];
    }
}
