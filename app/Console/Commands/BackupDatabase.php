<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('app:backup-database {--keep=14 : Number of daily backups to keep} {--connection= : Database connection to back up (defaults to the default one)}')]
#[Description('Copy the SQLite database into storage/app/backups and prune old copies')]
class BackupDatabase extends Command
{
    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('database.default');

        if (config("database.connections.{$connection}.driver") !== 'sqlite') {
            $this->components->warn('Only SQLite databases are backed up by this command. Use your database provider for backups.');

            return self::SUCCESS;
        }

        $database = config("database.connections.{$connection}.database");

        if ($database === ':memory:' || ! is_file($database)) {
            $this->components->error('No SQLite database file to back up.');

            return self::FAILURE;
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $target = $directory.'/database-'.now()->format('Y-m-d-His').'.sqlite';

        // VACUUM INTO writes a consistent snapshot even while the app is serving requests.
        DB::connection($connection)->statement('VACUUM INTO '.DB::connection($connection)->getPdo()->quote($target));

        $this->components->info("Backup written to {$target}");

        $backups = collect(File::glob($directory.'/database-*.sqlite'))->sort()->values();
        $stale = $backups->slice(0, max(0, $backups->count() - max(1, (int) $this->option('keep'))));
        $stale->each(fn (string $path) => File::delete($path));

        if ($stale->isNotEmpty()) {
            $this->components->info("Removed {$stale->count()} old backup(s).");
        }

        return self::SUCCESS;
    }
}
