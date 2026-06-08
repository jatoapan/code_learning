<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('challenge_id')->nullable()->constrained('challenges')->nullOnDelete();
            $table->text('input')->nullable();
            $table->text('expected_output');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_test_cases');
    }
};
