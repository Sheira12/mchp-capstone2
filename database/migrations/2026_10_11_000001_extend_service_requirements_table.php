<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends service_requirements to support the three-type eligibility model:
 *   seminar         – parishioner must have attended a seminar of this kind
 *   document        – parishioner must submit a document and have it verified
 *   sacrament_record – parishioner must have a matching sacramental_records row
 *
 * Existing rows (type = file|checkbox|text) are left untouched; those values
 * continue to work because this is an additive change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requirements', function (Blueprint $table) {
            // Broad eligibility type used for the pre-booking gate
            // Old values (file/checkbox/text) are kept for the per-booking
            // document-upload flow that already exists.
            if (!Schema::hasColumn('service_requirements', 'eligibility_type')) {
                $table->string('eligibility_type')->default('document')
                      ->after('is_active')
                      ->comment('seminar | document | sacrament_record | file | checkbox | text');
            }

            // For sacrament_record type: which sacrament is required (e.g. "baptism")
            if (!Schema::hasColumn('service_requirements', 'required_sacrament_type')) {
                $table->string('required_sacrament_type')->nullable()
                      ->after('eligibility_type');
            }

            // Whether a file proof is mandatory when declaring this requirement
            if (!Schema::hasColumn('service_requirements', 'requires_proof')) {
                $table->boolean('requires_proof')->default(true)
                      ->after('required_sacrament_type');
            }

            // Human-readable label for the service slug (denormalized for display)
            if (!Schema::hasColumn('service_requirements', 'service_slug')) {
                $table->string('service_slug')->nullable()
                      ->after('service_id')
                      ->comment('Matches the booking type slug; denormalized for convenience');
            }
        });

        // Back-fill service_slug from the related services table
        // Uses ANSI SQL UPDATE...FROM syntax (works on both PostgreSQL and MySQL 8+)
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                'UPDATE service_requirements
                 SET service_slug = s.slug
                 FROM services s
                 WHERE s.id = service_requirements.service_id
                   AND (service_requirements.service_slug IS NULL OR service_requirements.service_slug = \'\')'
            );
        } else {
            \Illuminate\Support\Facades\DB::statement(
                'UPDATE service_requirements sr
                 JOIN services s ON s.id = sr.service_id
                 SET sr.service_slug = s.slug
                 WHERE sr.service_slug IS NULL OR sr.service_slug = \'\''
            );
        }
    }

    public function down(): void
    {
        Schema::table('service_requirements', function (Blueprint $table) {
            $cols = ['eligibility_type', 'required_sacrament_type', 'requires_proof', 'service_slug'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('service_requirements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
