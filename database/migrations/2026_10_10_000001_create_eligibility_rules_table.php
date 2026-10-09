<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            /**
             * Criterion types:
             *   seminar_completed      – attended a seminar of a given type
             *   document_approved      – uploaded doc reviewed by admin (reuses booking_requirements)
             *   prerequisite_sacrament – sacramental_records or released certificate on file
             *   minimum_age            – parishioner age >= params.min_age
             *   parishioner_status     – parishioner.is_active = true
             *   other                  – free-text; admin manually marks as satisfied
             */
            $table->string('criterion_type');

            /**
             * Who the rule applies to:
             *   applicant  – the primary parishioner making the booking
             *   spouse     – the spouse (Wedding); checked manually by admin for now
             *   parents    – parents of the candidate (Baptism)
             *   godparents – godparents (Baptism)
             *   both       – both applicant and spouse must satisfy
             */
            $table->string('applies_to')->default('applicant');

            /**
             * JSON params — meaning depends on criterion_type:
             *   seminar_completed:      { "service_slug": "pre_baptismal", "validity_days": null }
             *   document_approved:      { "document_key": "birth_certificate" }
             *   prerequisite_sacrament: { "sacrament_type": "baptism" }
             *   minimum_age:            { "min_age": 7 }
             *   parishioner_status:     {}
             *   other:                  { "label": "Parents and godparents must attend seminar" }
             */
            $table->json('params')->nullable();

            $table->string('name');                            // short label shown to parishioner
            $table->text('description')->nullable();           // detailed note; seeded ones are PLACEHOLDERs
            $table->boolean('is_required')->default(true);     // if false, shows as "recommended"
            $table->boolean('is_placeholder')->default(false); // marks seeded rules awaiting parish confirmation
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'is_active', 'sort_order']);
            $table->index('criterion_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_rules');
    }
};
