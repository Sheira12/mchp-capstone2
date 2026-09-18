<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full audit trail for every certificate status transition.
 * Church certificates are legal documents — every change must be traceable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->cascadeOnDelete();
            $table->string('from_status')->nullable();   // null on initial creation
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index('certificate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_status_history');
    }
};
