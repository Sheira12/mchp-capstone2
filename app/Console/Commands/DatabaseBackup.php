<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature   = 'parish:backup-db';
    protected $description = 'Create a database backup (supports MySQL and PostgreSQL)';

    public function handle(): void
    {
        $connection = config('database.default');
        $filename   = 'backups/db-' . now()->format('Y-m-d-His') . '.sql';

        $output = match ($connection) {
            'pgsql'  => $this->dumpPostgres(),
            default  => $this->dumpMysql(),
        };

        if ($output) {
            Storage::disk('local')->put($filename, $output);
            $this->info("Database backed up to: storage/app/{$filename}");
            $this->cleanOldBackups();
        } else {
            $this->error('Database backup failed. Check that pg_dump / mysqldump is installed and credentials are correct.');
        }
    }

    private function dumpPostgres(): string|false
    {
        $cfg  = config('database.connections.pgsql');
        $host = escapeshellarg($cfg['host']);
        $port = escapeshellarg($cfg['port'] ?? '5432');
        $db   = escapeshellarg($cfg['database']);
        $user = escapeshellarg($cfg['username']);

        // Pass password via PGPASSWORD env var to avoid interactive prompt
        $cmd = "PGPASSWORD=" . escapeshellarg($cfg['password'])
             . " pg_dump --host={$host} --port={$port} --username={$user} --no-password {$db}";

        return shell_exec($cmd);
    }

    private function dumpMysql(): string|false
    {
        $cfg  = config('database.connections.mysql');
        $host = escapeshellarg($cfg['host']);
        $db   = escapeshellarg($cfg['database']);
        $user = escapeshellarg($cfg['username']);
        $pass = escapeshellarg($cfg['password']);

        $cmd = "mysqldump --user={$user} --password={$pass} --host={$host} {$db}";

        return shell_exec($cmd);
    }

    private function cleanOldBackups(): void
    {
        $files = Storage::disk('local')->files('backups');
        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified)) > 30) {
                Storage::disk('local')->delete($file);
                $this->line("Deleted old backup: {$file}");
            }
        }
    }
}
