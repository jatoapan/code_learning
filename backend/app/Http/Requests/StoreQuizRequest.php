<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Module;

class StoreQuizRequest extends FormRequest
{
    public function authorize()
    {
        $module = Module::findOrFail($this->route('id'));
        return $this->user()->can('update', $module->course);
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mode' => 'required|string|in:practice,exam',
            'time_limit_minutes' => 'nullable|integer',
            'passing_score' => 'required|integer|min:0|max:100',
        ];
    }
}
