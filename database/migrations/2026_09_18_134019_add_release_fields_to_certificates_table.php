<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds release tracking fields to certificates.
 * released_at and handled_by are needed for the audit trail and download gate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable()->after('status');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete()->after('released_at');
            // Daily request count support: track when the certificate was requested
            $table->timestamp('requested_at')->nullable()->after('handled_by');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['handled_by']);
            $table->dropColumn(['released_at', 'handled_by', 'requested_at']);
        });
    }
};
