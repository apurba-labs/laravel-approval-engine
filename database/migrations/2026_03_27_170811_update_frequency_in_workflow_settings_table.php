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
        // Use the Schema manager to get column info (Works in MySQL & SQLite)
        $columns = Schema::getColumnListing('workflow_settings');
        
        // Check if the column exists first (Safety check)
        if (in_array('frequency', $columns)) {
            Schema::table('workflow_settings', function (Blueprint $table) {
                // Laravel 11+ handles enum changes much better
                $table->enum('frequency', ['instant', 'daily', 'weekly', 'monthly'])
                    ->comment('instant, daily, weekly, monthly')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // Update any 'instant' records to 'daily' so the column change doesn't crash
        DB::table('workflow_settings')
            ->where('frequency', 'instant')
            ->update(['frequency' => 'daily']);

        // Now safely remove 'instant' from the enum definition
        Schema::table('workflow_settings', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])
                  ->comment('daily, weekly, monthly')
                  ->change();
        });
    }
};
