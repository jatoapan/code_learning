<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->uuidMorphs('endorseable'); // endorseable_id, endorseable_type
            $table->timestamps();

            $table->unique(['user_id', 'endorseable_id', 'endorseable_type'], 'endorsement_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('endorsements');
    }
};
