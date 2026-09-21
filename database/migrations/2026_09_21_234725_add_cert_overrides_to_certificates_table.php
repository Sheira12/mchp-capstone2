<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores parishioner-approved corrections directly on the certificate row.
 *
 * Why: On Render's ephemeral filesystem the PDF is wiped on every redeploy.
 * When re-generated on-demand, it reads from the DB — so corrections stored
 * here will always survive redeploys and are authoritative for PDF output.
 *
 * The JSON fields mirror sacramental_records columns.
 * CertificateService prefers cert_overrides values over sacramental_record
 * values when generating the PDF.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Single JSON blob holding any approved field overrides.
            // Keys: date_administered, celebrant, venue, godparents (array),
            //       sponsors (array), witnesses (array), register_number,
            //       page_number, line_number, notes, spouse_name, parents_names
            $table->json('cert_overrides')->nullable()->after('staff_notes');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('cert_overrides');
        });
    }
};
