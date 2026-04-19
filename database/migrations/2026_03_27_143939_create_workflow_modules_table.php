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
        Schema::create('workflow_modules', function (Blueprint $table) {
            $table->id();
            // Core identity
            $table->string('name');                 // Human readable (Requisition)
            $table->string('slug')->unique();       // system key (requisition)

            // Display / UI
            $table->string('label')->nullable();    // UI label (optional override)
            $table->string('icon')->nullable();     // for dashboard (heroicons etc.)

            // Control
            $table->boolean('is_active')->default(true);

            // Extensibility
            $table->json('config')->nullable();     // future: dynamic config (fields, behavior)

            // SaaS / future (safe to keep now)
            $table->string('source')->default('core'); // core | plugin | custom

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_modules');
    }
};
