<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            // Optional discount amount applied on top of the inclusions total.
            // Null = no discount configured; 0 = discount field shown but zero.
            $table->decimal('discount', 10, 2)->nullable()->default(null)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
