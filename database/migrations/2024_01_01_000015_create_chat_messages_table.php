<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->enum('sender', ['user', 'bot', 'staff']);
            $table->text('message');
            $table->string('intent')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
