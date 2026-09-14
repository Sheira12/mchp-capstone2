<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Subject-specific fields
            $table->date('preferred_date')->nullable()->after('message');
            $table->string('preferred_time', 10)->nullable()->after('preferred_date');
            // User-uploaded attachments: JSON array of {original_name, path, mime, size}
            $table->json('attachments')->nullable()->after('preferred_time');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['preferred_date', 'preferred_time', 'attachments']);
        });
    }
};
