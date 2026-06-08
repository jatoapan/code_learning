<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('votable_type');
            $table->uuid('votable_id');
            $table->tinyInteger('vote_type');
            $table->timestamps();

            $table->unique(['user_id', 'votable_type', 'votable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
