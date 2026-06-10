<?php
namespace App\Services;

use App\Models\ProfessorApplication;
use App\Enums\ProfessorApplicationStatus;
use App\Notifications\ApplicationReviewedNotification;

class ProfessorApplicationService
{
    public function reviewApplication(ProfessorApplication $app, array $data)
    {
        $app->status           = $data['status'];
        $app->reviewer_comment = $data['reviewer_comment'] ?? null;
        $app->reviewed_at      = now();
        $app->save();

        if ($data['status'] === 'approved') {
            $app->applicant->assignRole('professor');
        }

        if ($app->applicant) {
            $app->applicant->notify(new ApplicationReviewedNotification($app));
        }

        return $app;
    }
}
