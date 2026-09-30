<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * MigrateFilesToSupabase
 * ─────────────────────
 * One-time command to:
 *   1. Copy any files still present on the local `public` disk to Supabase.
 *   2. Report DB rows whose image_path / file_path no longer resolves on
 *      EITHER disk — those need to be manually re-uploaded after deploy.
 *
 * Run ONCE from Render's terminal (or locally before deploying) after the
 * Supabase env vars have been set:
 *
 *   php artisan storage:migrate-to-supabase
 *
 * Add --dry-run to see what would happen without touching anything.
 */
class MigrateFilesToSupabase extends Command
{
    protected $signature = 'storage:migrate-to-supabase
                            {--dry-run : Show what would be migrated without writing anything}';

    protected $description = 'Copy remaining local public-disk files to Supabase Storage and flag orphaned DB rows';

    // Tables and columns that store a relative file path
    private array $fileColumns = [
        ['table' => 'announcements',  'column' => 'image_path',  'label' => 'Announcement image'],
        ['table' => 'events',         'column' => 'image_path',  'label' => 'Event banner'],
        ['table' => 'gallery_items',  'column' => 'image_path',  'label' => 'Gallery photo'],
        ['table' => 'parishioners',   'column' => 'photo_path',  'label' => 'Parishioner photo',
         'skip_prefix' => 'data:'],   // base64 stored in DB — no file to migrate
        ['table' => 'payments',       'column' => 'proof_path',  'label' => 'Payment proof'],
        ['table' => 'certificates',   'column' => 'file_path',   'label' => 'Certificate PDF'],
        ['table' => 'certificates',   'column' => 'qr_code_path','label' => 'Certificate QR SVG'],
        ['table' => 'qr_codes',       'column' => 'qr_image_path','label'=> 'QR code SVG'],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun ? '── DRY RUN — no files will be written ──' : '── Migrating local files → Supabase ──');
        $this->newLine();

        $migrated = 0;
        $skipped  = 0;
        $orphaned = [];

        foreach ($this->fileColumns as $def) {
            $table    = $def['table'];
            $column   = $def['column'];
            $label    = $def['label'];
            $skipPrefix = $def['skip_prefix'] ?? null;

            $rows = DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->select('id', $column)
                ->get();

            foreach ($rows as $row) {
                $path = $row->$column;

                // Skip base64 data URIs (profile photos already stored in DB)
                if ($skipPrefix && str_starts_with($path, $skipPrefix)) {
                    continue;
                }

                // Skip paths that look like full URLs (already on Supabase)
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $skipped++;
                    continue;
                }

                // Check if already on Supabase
                try {
                    if (Storage::disk('supabase')->exists($path)) {
                        $this->line("  <fg=green>✓ Already on Supabase:</> [{$label}] {$path}");
                        $skipped++;
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->warn("  Could not check Supabase for {$path}: " . $e->getMessage());
                }

                // Check local public disk
                if (Storage::disk('public')->exists($path)) {
                    $this->line("  <fg=yellow>↑ Uploading:</> [{$label}] {$path}");

                    if (!$isDryRun) {
                        try {
                            $content = Storage::disk('public')->get($path);
                            Storage::disk('supabase')->put($path, $content, 'public');
                            $migrated++;
                        } catch (\Exception $e) {
                            $this->error("  FAILED: {$path} — " . $e->getMessage());
                        }
                    } else {
                        $migrated++; // count as "would migrate" in dry run
                    }
                } else {
                    // Not on local disk and not on Supabase → orphaned
                    $orphaned[] = [
                        'table'  => $table,
                        'id'     => $row->id,
                        'column' => $column,
                        'path'   => $path,
                        'label'  => $label,
                    ];
                    $this->line("  <fg=red>✗ Orphaned (lost):</> [{$label}] {$table}.id={$row->id} → {$path}");
                }
            }
        }

        // ── Summary ─────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info($isDryRun ? 'DRY RUN COMPLETE — nothing was written.' : 'MIGRATION COMPLETE');
        $this->info("  ✓ Migrated (uploaded to Supabase): {$migrated}");
        $this->info("  → Already on Supabase (skipped):  {$skipped}");
        $this->warn("  ✗ Orphaned (file lost, needs re-upload): " . count($orphaned));

        if (!empty($orphaned)) {
            $this->newLine();
            $this->warn('The following DB rows have image/file paths that no longer exist');
            $this->warn('on any disk. Their images were likely wiped by a prior Render');
            $this->warn('redeploy. You need to re-upload them through the admin panel:');
            $this->newLine();

            $this->table(
                ['Table', 'ID', 'Column', 'Lost path', 'Type'],
                array_map(fn($o) => [
                    $o['table'], $o['id'], $o['column'], $o['path'], $o['label']
                ], $orphaned)
            );
        }

        return self::SUCCESS;
    }
}
