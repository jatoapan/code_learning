<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FlashcardDeck;

class UpdateFlashcardDeckRequest extends FormRequest
{
    public function authorize()
    {
        $deck = FlashcardDeck::findOrFail($this->route('flashcard_deck'));
        return $deck->user_id === $this->user()->id || $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
