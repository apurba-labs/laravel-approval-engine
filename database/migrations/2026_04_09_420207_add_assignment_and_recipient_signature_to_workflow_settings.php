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
        Schema::table('workflow_settings', function (Blueprint $table) {
            // Assignment snapshot
            $table->string('assign_type')->nullable()->after('role');
            $table->text('assign_value')->nullable()->after('assign_type');

            // Deterministic batching / auth signature
            $table->text('recipient_signature_pattern')->nullable()->after('assign_value');

            $table->index(['module', 'assign_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_settings', function (Blueprint $table) {
            $table->dropColumn([
                'assign_type',
                'assign_value',
                'recipient_signature_pattern'
            ]);
        });
    }
};
