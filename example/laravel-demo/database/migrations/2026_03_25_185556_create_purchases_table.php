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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            // Standard columns for testing thresholds (v1.2)
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Polymorphic Owner Columns (To test our Signature Resolver)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // For Staff/Creator tests
            $table->unsignedBigInteger('admin_id')->nullable();   // For Admin tests
            
            // Workflow state columns
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
