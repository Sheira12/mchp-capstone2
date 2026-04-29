<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mass_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sunday, 6=Saturday');
            $table->time('time');
            $table->string('language')->default('Filipino');
            $table->string('celebrant')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->date('special_date')->nullable();
            $table->string('special_title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedules');
    }
};
