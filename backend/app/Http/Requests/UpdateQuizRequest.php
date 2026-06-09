<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Quiz;

class UpdateQuizRequest extends FormRequest
{
    public function authorize()
    {
        $quiz = Quiz::findOrFail($this->route('id'));
        $moduleItem = $quiz->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            return $this->user()->can('update', $moduleItem->module->course);
        }
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'mode' => 'sometimes|required|string|in:practice,exam',
            'time_limit_minutes' => 'nullable|integer',
            'passing_score' => 'sometimes|required|integer|min:0|max:100',
        ];
    }
}
