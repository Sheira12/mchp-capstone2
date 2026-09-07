<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes identified during the September 2026 audit.
 *
 * All indexes are additive only — no data changes, no column changes.
 * Safe to run on production without downtime.
 *
 * PostgreSQL note: We use standard Schema::table() which creates indexes
 * without CONCURRENTLY (Laravel doesn't support it natively). On a small
 * parish database these complete in milliseconds.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── payments ──────────────────────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            // booking_id: every $booking->payment hasOne lookup scans without this
            if (!$this->indexExists('payments', 'payments_booking_id_index')) {
                $table->index('booking_id', 'payments_booking_id_index');
            }
            // payment_method: filtered in admin index, reports groupBy
            if (!$this->indexExists('payments', 'payments_payment_method_index')) {
                $table->index('payment_method', 'payments_payment_method_index');
            }
            // transaction_type: filtered in reports debit/credit breakdown
            if (!$this->indexExists('payments', 'payments_transaction_type_index')) {
                $table->index('transaction_type', 'payments_transaction_type_index');
            }
            // created_at: used in Payment::boot() receipt_number count query
            if (!$this->indexExists('payments', 'payments_created_at_index')) {
                $table->index('created_at', 'payments_created_at_index');
            }
        });

        // ── bookings ──────────────────────────────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            // status standalone: Booking::pending()->count() and confirmed()->count()
            // The composite [scheduled_date, status] index exists but cannot serve
            // a single-column status query efficiently in all query plans.
            if (!$this->indexExists('bookings', 'bookings_status_index')) {
                $table->index('status', 'bookings_status_index');
            }
            // updated_at: dashboard filters completedBookings by updated_at >= $start
            if (!$this->indexExists('bookings', 'bookings_updated_at_index')) {
                $table->index('updated_at', 'bookings_updated_at_index');
            }
        });

        // ── parishioners ──────────────────────────────────────────────────────
        Schema::table('parishioners', function (Blueprint $table) {
            // is_active: filtered in index, reports, and search results
            if (!$this->indexExists('parishioners', 'parishioners_is_active_index')) {
                $table->index('is_active', 'parishioners_is_active_index');
            }
        });

        // ── notifications ─────────────────────────────────────────────────────
        // This table is polled every few seconds by both admin and parishioner
        // notification bells. Without these indexes each poll scans the full table.
        Schema::table('notifications', function (Blueprint $table) {
            // read_at: unreadNotifications() WHERE read_at IS NULL — polled constantly
            if (!$this->indexExists('notifications', 'notifications_read_at_index')) {
                $table->index('read_at', 'notifications_read_at_index');
            }
            // created_at: ORDER BY created_at DESC on every notification fetch
            if (!$this->indexExists('notifications', 'notifications_created_at_index')) {
                $table->index('created_at', 'notifications_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_booking_id_index');
            $table->dropIndex('payments_payment_method_index');
            $table->dropIndex('payments_transaction_type_index');
            $table->dropIndex('payments_created_at_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_status_index');
            $table->dropIndex('bookings_updated_at_index');
        });

        Schema::table('parishioners', function (Blueprint $table) {
            $table->dropIndex('parishioners_is_active_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_read_at_index');
            $table->dropIndex('notifications_created_at_index');
        });
    }

    /**
     * Check if an index already exists — prevents duplicate index errors on re-run.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $count = DB::selectOne(
                "SELECT COUNT(*) as cnt FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return ((int) ($count->cnt ?? 0)) > 0;
        }

        // MySQL / SQLite fallback
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
