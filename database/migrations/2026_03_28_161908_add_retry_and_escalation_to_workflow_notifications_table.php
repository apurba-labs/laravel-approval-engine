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
        Schema::table('workflow_notifications', function (Blueprint $table) {
            $table->integer('retry_count')->default(0)->after('error');
            $table->timestamp('next_retry_at')->nullable()->after('retry_count');

            $table->timestamp('escalate_at')->nullable()->after('next_retry_at');
            $table->string('escalate_to', 150)->nullable()->after('escalate_at');

            $table->index(['status', 'next_retry_at'], 'idx_notifications_retry_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_retry_queue');
            $table->dropColumn(['retry_count', 'next_retry_at', 'escalate_at', 'escalate_to']);
        });
    }
};
