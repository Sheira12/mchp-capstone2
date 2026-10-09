<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete(); // which service this seminar satisfies
            $table->string('title');
            $table->timestamp('scheduled_at');
            $table->string('venue')->nullable();
            $table->string('speaker')->nullable();
            $table->unsignedInteger('capacity')->default(30);
            $table->string('status')->default('scheduled'); // scheduled | completed | cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_id', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};
