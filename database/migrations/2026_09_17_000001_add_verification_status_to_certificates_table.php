<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds record_verification_status to certificates.
 *
 * Values:
 *   pending   — request created; admin has not yet verified a matching sacramental record
 *   verified  — a matching sacramental record exists AND is linked; PDF may be generated/downloaded
 *   unverified— no matching record found; request forwarded for manual staff review
 *
 * Also stores staff_notes for rejection/verification reasons visible to the parishioner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Verification status — controls whether download is allowed
            $table->enum('record_verification_status', ['pending', 'verified', 'unverified'])
                  ->default('pending')
                  ->after('status');

            // Reason/message shown to parishioner (e.g. "Record found: B-1998-064")
            $table->text('staff_notes')->nullable()->after('record_verification_status');

            // Index for dashboard queries
            $table->index('record_verification_status');
        });

        // Back-fill: certificates that already have a linked sacramental_record_id
        // and status = issued/released are considered verified.
        \DB::table('certificates')
            ->whereNotNull('sacramental_record_id')
            ->whereIn('status', ['issued', 'released'])
            ->update(['record_verification_status' => 'verified']);
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex(['record_verification_status']);
            $table->dropColumn(['record_verification_status', 'staff_notes']);
        });
    }
};
