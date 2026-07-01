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
        Schema::create('stage_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('started'); // started, submitted, evaluated
            $table->string('file_path')->nullable();
            $table->text('text_answer')->nullable();
            $table->decimal('obtained_mark', 8, 2)->nullable();
            $table->decimal('total_mark', 8, 2)->nullable();
            $table->json('quiz_results')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Enforce one-time participation per stage per candidate
            $table->unique(['job_application_id', 'pipeline_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_submissions');
    }
};
