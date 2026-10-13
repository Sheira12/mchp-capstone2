<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * parishioner_requirements
 *
 * Stores a parishioner's completion/declaration of a service requirement,
 * independent of any specific booking. Once verified here, the parishioner
 * satisfies that requirement for ALL future bookings of the same service.
 *
 * Sources:
 *   self_declared   – parishioner filled in the form themselves
 *   staff_recorded  – admin/staff entered it on the parishioner's behalf
 *   system_matched  – auto-created because a matching sacramental_record exists
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parishioner_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_requirement_id')->constrained('service_requirements')->cascadeOnDelete();

            // Lifecycle: pending → verified | rejected
            $table->string('status')->default('pending');

            $table->date('date_completed')->nullable();
            $table->string('venue_or_organizer')->nullable();   // e.g. "Diocese of San Pablo"
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable();           // Supabase Storage path

            // Who created this record and how
            $table->string('source')->default('self_declared'); // self_declared|staff_recorded|system_matched

            // Staff review fields
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // One declaration per parishioner per requirement — enforce uniqueness
            // (prevents duplicate submissions while one is pending or verified)
            $table->unique(['parishioner_id', 'service_requirement_id'], 'parish_req_unique');

            $table->index(['parishioner_id', 'status']);
            $table->index('service_requirement_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parishioner_requirements');
    }
};
