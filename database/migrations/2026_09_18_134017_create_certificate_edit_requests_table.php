<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores correction requests submitted by parishioners for their verified certificates.
 * Staff approve or reject each request before the change flows into the certificate PDF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            // What the parishioner wants to change (JSON key-value diff)
            $table->json('requested_changes');
            // Optional message from parishioner explaining the correction
            $table->text('parishioner_note')->nullable();
            // Status of the edit request
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // Staff decision
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('staff_response')->nullable();
            $table->timestamps();

            $table->index(['certificate_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_edit_requests');
    }
};
