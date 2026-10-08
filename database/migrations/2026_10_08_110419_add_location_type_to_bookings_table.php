<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds location_type (in_church / off_site) to bookings.
 * The existing `address` column already serves as the off-site address.
 * Existing rows default to 'in_church' — safe for existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('location_type')->default('in_church')->after('address');
            // contact_person for off-site services (funeral home, house, etc.)
            $table->string('contact_person')->nullable()->after('location_type');
            $table->string('contact_phone')->nullable()->after('contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['location_type', 'contact_person', 'contact_phone']);
        });
    }
};
