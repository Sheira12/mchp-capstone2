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
            DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new','read','replied','in_progress','resolved','closed') NOT NULL DEFAULT 'new'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL stores enums as strings with CHECK constraints
            DB::statement("ALTER TABLE inquiries DROP CONSTRAINT IF EXISTS inquiries_status_check");
            DB::statement("ALTER TABLE inquiries ADD CONSTRAINT inquiries_status_check CHECK (status IN ('new','read','replied','in_progress','resolved','closed'))");
        }
        // SQLite: no constraint change needed
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE inquiries SET status='replied' WHERE status IN ('in_progress','resolved','closed')");
            DB::statement("ALTER TABLE inquiries MODIFY COLUMN status ENUM('new','read','replied') NOT NULL DEFAULT 'new'");
        } elseif ($driver === 'pgsql') {
            DB::statement("UPDATE inquiries SET status='replied' WHERE status IN ('in_progress','resolved','closed')");
            DB::statement("ALTER TABLE inquiries DROP CONSTRAINT IF EXISTS inquiries_status_check");
            DB::statement("ALTER TABLE inquiries ADD CONSTRAINT inquiries_status_check CHECK (status IN ('new','read','replied'))");
        }
    }
};
