<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePracticeQuizRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quiz_id' => 'required|exists:quizzes,id',
            'question_count' => 'nullable|integer|min:1'
        ];
    }
}
