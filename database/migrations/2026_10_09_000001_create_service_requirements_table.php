<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // "Baptismal Certificate"
            $table->text('description')->nullable();         // helper text shown to parishioner
            $table->string('type')->default('file');         // file | checkbox | text
            $table->boolean('is_required')->default(true);   // required vs optional
            $table->string('accepted_file_types')            // "pdf,jpg,png" — only for type=file
                  ->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requirements');
    }
};
