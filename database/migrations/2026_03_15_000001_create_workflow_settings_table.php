<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workflow_settings')) {
            Schema::create('workflow_settings', function (Blueprint $table) {
                $table->id();
                $table->string('module', 150)->comment('Module for which this setting applies');
                $table->string('role')->comment('Role for which this setting applies');
                $table->enum('frequency', ['daily', 'weekly', 'monthly'])->comment('daily, weekly, monthly');
                $table->tinyInteger('weekly_day')->nullable()->comment('0=Sunday, 1=Monday ... 6=Saturday');
                $table->tinyInteger('monthly_date')->nullable()->comment('1-31');
                $table->time('send_time')->default('12:00:00')->comment('HH:mm');
                $table->string('timezone', 150)->default('Asia/Dhaka')->comment('Asia/Dhaka');
                $table->timestamp('last_run_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['role', 'module'], 'uidx_role_module');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_settings');
    }
};
