<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();           // OP-2026-00001
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_package_id')->nullable()->constrained('service_packages')->nullOnDelete();

            // Line items: [{"label":"Wedding Mass","amount":3000},{"label":"Flowers Package","amount":2000}]
            $table->json('line_items')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('fees', 10, 2)->default(0);        // misc fees
            $table->decimal('total', 10, 2)->default(0);

            // Status lifecycle: pending → paid | cancelled | expired
            $table->string('status')->default('pending');

            $table->timestamp('expires_at')->nullable();       // 24h after creation
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['parishioner_id', 'status']);
            $table->index('booking_id');
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
