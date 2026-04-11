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
        Schema::create('score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('field_name', [
                'spirit_score',
                'theory_score',
                'practice_score',
                'final_score',
                'result_status',
            ]);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->timestamp('deadline_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('late_by_minutes')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'user_id']);
            $table->index(['field_name', 'is_late']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_histories');
    }
};
