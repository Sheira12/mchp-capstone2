<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Expand from VARCHAR(6) to VARCHAR(255) to store bcrypt hashes
            $table->string('two_factor_code', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_code', 6)->nullable()->change();
        });
    }
};
