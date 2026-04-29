<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->default('Cabuyao');
            $table->string('province')->default('Laguna');
            $table->string('contact_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('family_name');
            $table->index('barangay');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
