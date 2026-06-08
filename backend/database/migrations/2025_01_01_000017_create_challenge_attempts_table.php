<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('challenge_id')->nullable()->constrained('challenges')->nullOnDelete();
            $table->text('submitted_code');
            $table->unsignedInteger('language_id');
            $table->string('status', 50);
            $table->unsignedInteger('test_cases_passed')->default(0);
            $table->unsignedInteger('test_cases_total')->default(0);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->unsignedInteger('execution_memory_kb')->nullable();
            $table->text('stdout')->nullable();
            $table->text('stderr')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
    }
};
