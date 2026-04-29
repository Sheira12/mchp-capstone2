<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature   = 'parish:backup-db';
    protected $description = 'Create a database backup';

    public function handle(): void
    {
        $filename  = 'backups/db-' . now()->format('Y-m-d-His') . '.sql';
        $dbName    = config('database.connections.mysql.database');
        $dbUser    = config('database.connections.mysql.username');
        $dbPass    = config('database.connections.mysql.password');
        $dbHost    = config('database.connections.mysql.host');

        $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName}";
        $output  = shell_exec($command);

        if ($output) {
            Storage::disk('local')->put($filename, $output);
            $this->info("Database backed up to: {$filename}");

            // Clean up backups older than 30 days
            $this->cleanOldBackups();
        } else {
            $this->error('Database backup failed.');
        }
    }

    private function cleanOldBackups(): void
    {
        $files = Storage::disk('local')->files('backups');
        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified)) > 30) {
                Storage::disk('local')->delete($file);
            }
        }
    }
}
