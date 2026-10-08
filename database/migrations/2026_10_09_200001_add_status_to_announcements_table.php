<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // draft | published | scheduled
            $table->string('status')->default('draft')->after('is_published');
            // is_pinned was in the model fillable but may be missing from the column
            if (!Schema::hasColumn('announcements', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('status');
            }
            // scheduled_at: future publish date (null = immediate on publish)
            if (!Schema::hasColumn('announcements', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
        });

        // Backfill: derive status from existing is_published + published_at
        DB::statement("
            UPDATE announcements
            SET status = CASE
                WHEN is_published = TRUE  THEN 'published'
                WHEN is_published = FALSE AND published_at IS NOT NULL AND published_at > NOW() THEN 'scheduled'
                ELSE 'draft'
            END
        ");

        // One-time fix: strip trailing backslash from titles
        DB::statement("UPDATE announcements SET title = RTRIM(title, '\\') WHERE title LIKE '%\\'");
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('status');
            if (Schema::hasColumn('announcements', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
