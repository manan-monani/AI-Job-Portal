<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_stage_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, in_progress, passed, failed, skipped
            $table->unsignedInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->unique(['pipeline_stage_id', 'job_application_id'], 'stage_application_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_stage_statuses');
    }
};
