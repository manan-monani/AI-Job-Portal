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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('ats_state')->default('not_started'); // pending, processing, scored, failed
            $table->decimal('ats_score_cached', 8, 2)->nullable();
            $table->boolean('ats_passed_cached')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['ats_state', 'ats_score_cached', 'ats_passed_cached']);
        });
    }
};
