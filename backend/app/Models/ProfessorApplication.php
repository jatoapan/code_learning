<?php

namespace App\Models;

use App\Enums\ProfessorApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'reviewer_id',
        'status',
        'motivation',
        'qualifications',
        'reviewer_comment',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ProfessorApplicationStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
