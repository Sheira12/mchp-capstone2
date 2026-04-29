<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('template')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->nullableMorphs('related'); // creates related_type + related_id index
            $table->timestamps();

            $table->index('to_email');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
