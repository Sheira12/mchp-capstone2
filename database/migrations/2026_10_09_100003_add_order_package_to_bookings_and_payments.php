<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('service_package_id')->nullable()->after('service_fee')
                  ->constrained('service_packages')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->after('service_package_id')
                  ->constrained('orders')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('certificate_id')
                  ->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['service_package_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['service_package_id', 'order_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
