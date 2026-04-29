<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parishioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('civil_status', ['single', 'married', 'widowed', 'separated', 'annulled'])->nullable();
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->default('Cabuyao');
            $table->string('province')->default('Laguna');
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_head_of_family')->default(false);
            $table->string('relationship_to_head')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['last_name', 'first_name']);
            $table->index('barangay');
            $table->index('family_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parishioners');
    }
};
