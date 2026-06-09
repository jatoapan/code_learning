<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Quiz;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize()
    {
        $quiz = Quiz::findOrFail($this->route('quizId'));
        $moduleItem = $quiz->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            return $this->user()->can('update', $moduleItem->module->course);
        }
        return true;
    }

    public function rules()
    {
        return [
            'question_text' => 'required|string',
            'type' => 'required|string|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required|array', 
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
        ];
    }
}
