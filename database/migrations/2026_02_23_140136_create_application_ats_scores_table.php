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
        Schema::create('application_ats_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->string('pass_reason')->nullable();
            $table->json('criteria_breakdown')->nullable();
            $table->string('scoring_version')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->index(['job_post_id', 'pipeline_stage_id', 'passed', 'total_score'], 'ats_score_ranking_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_ats_scores');
    }
};
