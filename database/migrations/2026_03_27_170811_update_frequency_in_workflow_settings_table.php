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
        $column = DB::select("SHOW COLUMNS FROM workflow_settings WHERE Field = 'frequency'")[0];
        $type = $column->Type; // e.g. enum('daily','weekly','monthly')

        if (!str_contains($type, 'instant')) {
            Schema::table('workflow_settings', function (Blueprint $table) {
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
       Schema::table('workflow_settings', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])
                  ->comment('daily, weekly, monthly')
                  ->change();
        });
    }
};
