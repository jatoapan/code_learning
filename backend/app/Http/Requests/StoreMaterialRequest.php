<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:pdf,video_link,ppt,pptx,video',
            'content' => 'nullable|string', // URL o texto
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,mp4,avi,mkv|max:51200', // Hasta 50MB
            'estimated_minutes' => 'nullable|integer',
            'is_required' => 'nullable|boolean',
        ];
    }
}
