<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 'debit'  = money going OUT from parishioner (service fee / payment)
            // 'credit' = money coming IN to parishioner account (refund / adjustment)
            $table->enum('transaction_type', ['debit', 'credit'])
                  ->default('debit')
                  ->after('payment_method');
        });

        // Back-fill existing rows: refunded/voided → credit; everything else → debit
        DB::table('payments')
            ->whereIn('status', ['refunded', 'voided'])
            ->update(['transaction_type' => 'credit']);

        DB::table('payments')
            ->whereNotIn('status', ['refunded', 'voided'])
            ->update(['transaction_type' => 'debit']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
