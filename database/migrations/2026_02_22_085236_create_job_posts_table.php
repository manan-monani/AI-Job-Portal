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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('description');
            $table->string('salary_type')->default('negotiable'); // negotiable, non-negotiable
            $table->decimal('min_salary', 15, 2)->nullable();
            $table->decimal('max_salary', 15, 2)->nullable();
            $table->decimal('min_experience', 5, 2);
            $table->decimal('max_experience', 5, 2);
            $table->string('job_type'); // onsite, remote, hybrid
            $table->string('location')->nullable();
            $table->date('deadline');
            $table->string('employment_type'); // full-time, part-time, contract, internship
            $table->string('status')->default('draft'); // draft, published, hidden
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
