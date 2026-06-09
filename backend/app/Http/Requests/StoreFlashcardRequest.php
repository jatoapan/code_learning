<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FlashcardDeck;

class StoreFlashcardRequest extends FormRequest
{
    public function authorize()
    {
        $deck = FlashcardDeck::findOrFail($this->route('id'));
        return $deck->user_id === $this->user()->id || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'question_text' => 'required|string',
            'answer_text' => 'required|string',
        ];
    }
}
