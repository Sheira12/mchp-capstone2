<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->morphs('qr_codeable');
            $table->string('token', 64)->unique();
            $table->string('verification_url');
            $table->string('qr_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
