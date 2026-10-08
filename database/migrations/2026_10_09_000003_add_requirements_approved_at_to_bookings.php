<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Set when admin approves all required items — the gate checks this
            $table->timestamp('requirements_approved_at')->nullable()->after('admin_notes');
            $table->foreignId('requirements_approved_by')->nullable()->after('requirements_approved_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['requirements_approved_by']);
            $table->dropColumn(['requirements_approved_at', 'requirements_approved_by']);
        });
    }
};
