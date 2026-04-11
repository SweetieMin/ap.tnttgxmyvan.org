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
        Schema::table('scores', function (Blueprint $table) {
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('spirit_score', 4, 2)->nullable();
            $table->decimal('theory_score', 4, 2)->nullable();
            $table->decimal('practice_score', 4, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('result_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->foreignId('spirit_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('spirit_updated_at')->nullable();
            $table->foreignId('theory_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('theory_updated_at')->nullable();
            $table->foreignId('practice_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('practice_updated_at')->nullable();

            $table->unique(['schedule_id', 'user_id']);
            $table->index(['schedule_id', 'result_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropUnique('scores_schedule_id_user_id_unique');
            $table->dropIndex('scores_schedule_id_result_status_index');
            $table->dropConstrainedForeignId('practice_updated_by');
            $table->dropColumn('practice_updated_at');
            $table->dropConstrainedForeignId('theory_updated_by');
            $table->dropColumn('theory_updated_at');
            $table->dropConstrainedForeignId('spirit_updated_by');
            $table->dropColumn('spirit_updated_at');
            $table->dropColumn(['result_status', 'final_score', 'practice_score', 'theory_score', 'spirit_score']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
