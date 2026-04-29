<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('parishioner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_change_logs');
    }
};
