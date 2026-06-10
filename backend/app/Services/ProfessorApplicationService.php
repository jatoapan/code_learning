<?php
namespace App\Services;

use App\Models\ProfessorApplication;
use App\Enums\ProfessorApplicationStatus;
use App\Notifications\ApplicationReviewedNotification;

class ProfessorApplicationService
{
    public function getPaginatedApplications()
    {
        return ProfessorApplication::with('applicant:id,name', 'reviewer:id,name')->paginate(15);
    }

    public function getUserApplications($userId)
    {
        return ProfessorApplication::where('applicant_id', $userId)->get();
    }

    public function getApplication($id)
    {
        return ProfessorApplication::findOrFail($id);
    }

    public function createApplication(array $data, $user)
    {
        $app = new ProfessorApplication();
        $app->applicant_id  = $user->id;
        $app->motivation    = $data['motivation'];
        $app->qualifications = $data['qualifications'];
        $app->status        = ProfessorApplicationStatus::Pending->value;
        $app->save();

        return $app;
    }

    public function assignReviewer(ProfessorApplication $app, $user)
    {
        $app->reviewer_id = $user->id;
        $app->status      = ProfessorApplicationStatus::UnderReview->value;
        $app->save();
        
        return $app;
    }

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
