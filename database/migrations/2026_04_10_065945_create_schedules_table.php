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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_subject_id')->constrained('classroom_subject')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('type', ['study', 'exam', 'camp', 'reminder'])->default('study');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');

            $table->date('date_end_spirit')->nullable();
            $table->date('date_end_practice_theory')->nullable();

            $table->boolean('have_record')->default(true);
            $table->timestamps();

            $table->index(['date', 'status']);
            $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
