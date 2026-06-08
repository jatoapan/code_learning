<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('itemable_type');
            $table->string('itemable_id', 36);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_items');
    }
};
