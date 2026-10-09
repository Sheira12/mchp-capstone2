<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligibility_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parishioner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('waived_by')->constrained('users')->restrictOnDelete(); // super_admin only
            $table->text('reason');           // required — must explain why rule is waived
            $table->timestamp('waived_at');
            $table->timestamp('expires_at')->nullable(); // optional — waiver for a limited time
            $table->timestamps();

            // A rule can only be waived once per parishioner per booking
            $table->unique(['eligibility_rule_id', 'parishioner_id', 'booking_id'], 'elig_waiver_unique');
            $table->index(['parishioner_id']);
            $table->index(['eligibility_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_waivers');
    }
};
