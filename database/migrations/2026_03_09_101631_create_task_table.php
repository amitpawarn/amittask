<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('task_name');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('project_name');
            $table->enum('status', ['pending','on-going','testing','done','complete', 'rework'])->default('pending');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task');
    }
};
