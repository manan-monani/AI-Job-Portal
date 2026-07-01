<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('internship_duration')->nullable()->after('employment_type');
            $table->string('contract_duration')->nullable()->after('internship_duration');
            $table->string('working_hours')->nullable()->after('contract_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['internship_duration', 'contract_duration', 'working_hours']);
        });
    }
};
