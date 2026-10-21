<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make bookings.scheduled_date nullable so that "stub" bookings created
 * during the requirements-first flow can be saved without a date until
 * the parishioner completes their document submissions and gets approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('scheduled_date')->nullable(false)->change();
        });
    }
};
