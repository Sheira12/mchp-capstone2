<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Wedding / Marriage sponsor names — used on marriage certificates
            $table->string('ninong_name')->nullable()->after('notes');
            $table->string('ninang_name')->nullable()->after('ninong_name');
            // Secondary/pair info for marriage bookings
            $table->string('spouse_name')->nullable()->after('ninang_name');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['ninong_name', 'ninang_name', 'spouse_name']);
        });
    }
};
