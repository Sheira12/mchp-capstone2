<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL: modify ENUM column directly
            DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('gcash','maya','cash','bank','card') NOT NULL DEFAULT 'cash'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Laravel's enum is stored as a string CHECK constraint
            // Drop existing constraint and add new one
            DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('gcash','maya','cash','bank','card'))");
        }
        // SQLite: no constraint change needed (enums are just strings)
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE payments SET payment_method = 'cash' WHERE payment_method = 'card'");
            DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('gcash','maya','cash','bank') NOT NULL DEFAULT 'cash'");
        } elseif ($driver === 'pgsql') {
            DB::statement("UPDATE payments SET payment_method = 'cash' WHERE payment_method = 'card'");
            DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('gcash','maya','cash','bank'))");
        }
    }
};
