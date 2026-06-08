<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('applicant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->text('motivation');
            $table->text('qualifications')->nullable();
            $table->text('reviewer_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('applicant_id');
            $table->index('reviewer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professor_applications');
    }
};
