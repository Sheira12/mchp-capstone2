<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['credit', 'debit']); // credit = income, debit = expense
            $table->string('category', 100);           // e.g. Donation, Mass Stipend, Utilities
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('entry_date');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'entry_date']);
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
