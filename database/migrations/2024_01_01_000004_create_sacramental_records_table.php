<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sacramental_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spouse_parishioner_id')->nullable()->constrained('parishioners')->nullOnDelete();
            $table->enum('type', ['baptism', 'first_communion', 'confirmation', 'marriage', 'death_burial']);
            $table->date('date_administered');
            $table->string('celebrant');
            $table->string('venue')->nullable();
            $table->string('register_number')->nullable();
            $table->string('page_number')->nullable();
            $table->string('line_number')->nullable();
            $table->json('godparents')->nullable();
            $table->json('witnesses')->nullable();
            $table->json('sponsors')->nullable();
            $table->json('document_references')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parishioner_id', 'type']);
            $table->index('type');
            $table->index('date_administered');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sacramental_records');
    }
};
