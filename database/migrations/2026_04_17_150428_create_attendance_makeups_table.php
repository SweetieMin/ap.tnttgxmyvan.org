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
        Schema::create('attendance_makeups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_attendance_id')->unique()->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('makeup_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_attendance_status', 32);
            $table->enum('status', ['scheduled', 'completed', 'missed'])->default('scheduled');
            $table->enum('attendance_status', [
                'on_time',
                'late_excused',
                'late_unexcused',
                'absent_excused',
                'absent_unexcused',
            ])->nullable();
            $table->text('attendance_note')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('spirit_score', 4, 2)->nullable();
            $table->decimal('theory_score', 4, 2)->nullable();
            $table->decimal('practice_score', 4, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('result_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['makeup_session_id', 'user_id']);
            $table->index(['makeup_session_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_makeups');
    }
};
