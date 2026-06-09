<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\QuizQuestion;

class UpdateQuizAnswersRequest extends FormRequest
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
            'answers' => 'required|array',
            'answers.*.id' => 'nullable|exists:quiz_answers,id',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ];
    }
}
