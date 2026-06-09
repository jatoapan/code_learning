<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\QuizQuestion;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize()
    {
        $question = QuizQuestion::findOrFail($this->route('id'));
        $moduleItem = $question->quiz->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            return $this->user()->can('update', $moduleItem->module->course);
        }
        return true;
    }

    public function rules()
    {
        return [
            'question_text' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|in:multiple_choice,true_false',
            'points' => 'sometimes|required|integer|min:1',
            'explanation' => 'nullable|string',
        ];
    }
}
