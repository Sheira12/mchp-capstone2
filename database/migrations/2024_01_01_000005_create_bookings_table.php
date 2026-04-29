<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->string('booking_type');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('reference_number')->unique();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['scheduled_date', 'status']);
            $table->index('parishioner_id');
            $table->index('booking_type');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
