<?php
namespace App\Policies;

use App\Models\User;
use App\Models\FlashcardDeck;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlashcardDeckPolicy
{
    use HandlesAuthorization;

    public function view(User $user, FlashcardDeck $deck)
    {
        return $user->id === $deck->user_id || $user->hasRole('admin');
    }

    public function update(User $user, FlashcardDeck $deck)
    {
        return $user->id === $deck->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, FlashcardDeck $deck)
    {
        return $user->id === $deck->user_id || $user->hasRole('admin');
    }
}
