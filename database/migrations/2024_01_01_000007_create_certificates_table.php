<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sacramental_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('certificate_number')->unique();
            $table->date('issued_date');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose')->nullable();
            $table->string('file_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['draft', 'issued', 'released'])->default('draft');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('parishioner_id');
            $table->index('type');
            $table->index('certificate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
