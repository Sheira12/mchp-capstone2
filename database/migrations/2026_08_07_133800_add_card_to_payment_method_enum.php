<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the ENUM to include 'card'
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('gcash','maya','cash','bank','card') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        // Revert — first update any 'card' rows back to 'cash' to avoid constraint violation
        DB::statement("UPDATE payments SET payment_method = 'cash' WHERE payment_method = 'card'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('gcash','maya','cash','bank') NOT NULL DEFAULT 'cash'");
    }
};
