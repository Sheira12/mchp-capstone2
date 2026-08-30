<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parishioners', function (Blueprint $table) {
            // Change photo_path from VARCHAR(255) to TEXT to store base64 data URIs
            $table->text('photo_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parishioners', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->change();
        });
    }
};
