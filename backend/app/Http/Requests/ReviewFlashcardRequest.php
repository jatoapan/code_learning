<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Flashcard;

class ReviewFlashcardRequest extends FormRequest
{
    public function authorize()
    {
        $flashcard = Flashcard::findOrFail($this->route('id'));
        return $flashcard->deck->user_id === $this->user()->id || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'quality' => 'required|integer|min:0|max:5',
        ];
    }
}
