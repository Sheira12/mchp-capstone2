<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Reference number submitted by parishioner after paying via GCash/Maya
            $table->string('submitted_reference')->nullable()->after('gateway_reference');
            // Proof of payment screenshot path
            $table->string('proof_path')->nullable()->after('submitted_reference');
            // Contact number of payer
            $table->string('payer_contact')->nullable()->after('proof_path');
            // Admin who verified the payment
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('payer_contact');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            // Rejection reason if admin rejects proof
            $table->text('rejection_reason')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_reference',
                'proof_path',
                'payer_contact',
                'verified_by',
                'verified_at',
                'rejection_reason',
            ]);
        });
    }
};
