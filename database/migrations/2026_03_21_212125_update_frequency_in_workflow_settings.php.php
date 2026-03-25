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
            // We change the enum to include 'instant'
            $table->enum('frequency', ['instant', 'daily', 'weekly', 'monthly'])
                ->default('daily')
                ->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_settings', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])
                ->default('daily')
                ->change();
        });
    }
};
