<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // system, sorting, assessment, interview
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->string('system_key')->nullable(); // cv_received_mail, onboard_mail
            $table->boolean('is_enabled')->default(true);
            $table->boolean('send_mail_on_trigger')->default(false);
            $table->timestamps();

            $table->index(['job_post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
