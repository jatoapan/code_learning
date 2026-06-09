<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Flashcard;

class UpdateFlashcardRequest extends FormRequest
{
    public function authorize()
    {
        $flashcard = Flashcard::findOrFail($this->route('id'));
        return $flashcard->deck->user_id === $this->user()->id || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'question_text' => 'sometimes|required|string',
            'answer_text' => 'sometimes|required|string',
        ];
    }
}
