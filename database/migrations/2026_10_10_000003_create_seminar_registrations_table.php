<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered'); // registered | attended | absent | cancelled
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->string('qr_token')->unique()->nullable();  // for QR check-in
            $table->string('cert_path')->nullable();           // optional completion cert on Supabase
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete(); // admin who marked attendance
            $table->timestamps();

            // One registration per parishioner per seminar
            $table->unique(['seminar_id', 'parishioner_id']);
            $table->index(['parishioner_id', 'status']);
            $table->index('qr_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_registrations');
    }
};
