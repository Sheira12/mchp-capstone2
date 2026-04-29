<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->string('postal_code', 10)->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->dropColumn('postal_code');
        });
    }
};
