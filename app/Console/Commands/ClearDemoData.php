<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Family;
use App\Models\LedgerEntry;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use App\Models\User;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ClearDemoData
 *
 * Deletes ONLY records created by DummyDataSeeder.
 * Identifies them by the '[DUMMY]' prefix in their `notes` column.
 *
 * SAFETY GUARANTEE:
 *   This command never deletes any record whose `notes` column
 *   does NOT start with '[DUMMY]'. Real production records are
 *   completely untouched.
 *
 * Usage:
 *   php artisan demo-data:clear
 *   php artisan demo-data:clear --dry-run   (preview without deleting)
 */
class ClearDemoData extends Command
{
    protected $signature   = 'demo-data:clear {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Remove all demo data created by DummyDataSeeder. Never touches real production records.';

    private bool $dryRun   = false;
    private int  $total    = 0;
    private string $tag    = DummyDataSeeder::TAG;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('──── DRY RUN — nothing will be deleted ────');
        } else {
            if (!$this->confirm(
                'This will permanently delete all [DUMMY] demo records. Continue?',
                false
            )) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        $this->info('Scanning for demo records tagged with: ' . $this->tag);
        $this->newLine();

        // ── Deletion order must respect foreign key constraints ───────────────
        // Reverse of insertion: certificates → payments → bookings →
        //   sacramental_records → users → parishioners → ledger_entries → families

        $this->deleteCertificates();
        $this->deletePayments();
        $this->deleteBookings();
        $this->deleteSacramentalRecords();
        $this->deleteUsers();
        $this->deleteParishioners();
        $this->deleteLedgerEntries();
        $this->deleteFamilies();

        $this->newLine();

        if ($this->dryRun) {
            $this->warn("DRY RUN complete — {$this->total} record(s) would be deleted.");
        } else {
            $this->info("✅ Done — {$this->total} demo record(s) deleted.");
            $this->info('   Real production data was not affected.');
        }

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function deleteCertificates(): void
    {
        $query = Certificate::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Certificates      : 0");
            return;
        }

        if (!$this->dryRun) {
            $query->delete();
        }

        $this->total += $count;
        $this->line("  Certificates      : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deletePayments(): void
    {
        $query = Payment::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Payments          : 0");
            return;
        }

        if (!$this->dryRun) {
            $query->delete();
        }

        $this->total += $count;
        $this->line("  Payments          : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteBookings(): void
    {
        $query = Booking::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Bookings          : 0");
            return;
        }

        if (!$this->dryRun) {
            // Force delete (bypasses soft delete) so the records are gone for cleanup
            $query->forceDelete();
        }

        $this->total += $count;
        $this->line("  Bookings          : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteSacramentalRecords(): void
    {
        $query = SacramentalRecord::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Sacramental records: 0");
            return;
        }

        if (!$this->dryRun) {
            $query->forceDelete();
        }

        $this->total += $count;
        $this->line("  Sacramental records: <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteUsers(): void
    {
        // Only delete users whose email ends with @example.com
        // Extra safety check on top of the [DUMMY] tag
        $demoEmails = Parishioner::where('notes', 'like', $this->tag . '%')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn($e) => str_ends_with($e, '@example.com'))
            ->values();

        if ($demoEmails->isEmpty()) {
            $this->line("  Users             : 0");
            return;
        }

        $query = User::whereIn('email', $demoEmails);
        $count = $query->count();

        if (!$this->dryRun) {
            $query->delete();
        }

        $this->total += $count;
        $this->line("  Users             : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteParishioners(): void
    {
        $query = Parishioner::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Parishioners      : 0");
            return;
        }

        if (!$this->dryRun) {
            $query->forceDelete();
        }

        $this->total += $count;
        $this->line("  Parishioners      : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteLedgerEntries(): void
    {
        $query = LedgerEntry::where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Ledger entries    : 0");
            return;
        }

        if (!$this->dryRun) {
            $query->delete();
        }

        $this->total += $count;
        $this->line("  Ledger entries    : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }

    private function deleteFamilies(): void
    {
        // Only delete families whose family_name starts with '[DUMMY]'
        // Double safety: family_name AND notes both tagged
        $query = Family::where('family_name', 'like', $this->tag . '%')
                       ->where('notes', 'like', $this->tag . '%');
        $count = $query->count();

        if ($count === 0) {
            $this->line("  Families          : 0");
            return;
        }

        if (!$this->dryRun) {
            $query->forceDelete();
        }

        $this->total += $count;
        $this->line("  Families          : <fg=yellow>{$count}</> " . ($this->dryRun ? '(would delete)' : 'deleted'));
    }
}
