<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_requirement_id')->constrained('service_requirements')->cascadeOnDelete();

            // Submission details
            $table->string('status')->default('pending');    // pending|approved|needs_revision
            $table->string('file_path')->nullable();         // Supabase Storage path (type=file)
            $table->text('text_value')->nullable();          // answer (type=text or checkbox)
            $table->text('parishioner_note')->nullable();    // note from parishioner with submission

            // Admin review
            $table->text('admin_remark')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('service_requirement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requirements');
    }
};
