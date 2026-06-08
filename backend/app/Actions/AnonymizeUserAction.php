<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnonymizeUserAction
{
    /**
     * Executes the immediate anonymization and soft deletion of a user.
     * GDPR Compliance: Right to be forgotten.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Revoke all active sessions/tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // 2. Hash email irreversibly to avoid unique constraint collisions but remove PII
            $anonymizedEmail = hash('sha256', $user->email) . '@anonymized.prolecom.com';

            // 3. Overwrite PII fields
            $user->update([
                'name' => 'Usuario Anonimizado',
                'email' => $anonymizedEmail,
                'avatar_path' => null,
                'password' => 'anonymized_and_disabled',
                'status' => 'deactivated', // Mapped to UserStatus::DEACTIVATED in Enum
            ]);

            // 4. Soft delete the user immediately
            $user->delete();

            // 5. Activity log should ideally be recorded here
            // ActivityLog::create(['action' => 'user_anonymized', ...])
        });
    }
}
