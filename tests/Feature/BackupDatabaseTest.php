<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->directory = sys_get_temp_dir().'/rw-backup-'.uniqid();
    File::ensureDirectoryExists($this->directory);

    $this->databaseFile = $this->directory.'/app.sqlite';
    $pdo = new PDO('sqlite:'.$this->databaseFile);
    $pdo->exec('create table notes (id integer primary key, body text)');
    $pdo->exec("insert into notes (body) values ('hello')");
    unset($pdo);

    config([
        'database.connections.backup_test' => ['driver' => 'sqlite', 'database' => $this->databaseFile, 'prefix' => '', 'foreign_key_constraints' => true],
    ]);

    app()->useStoragePath($this->directory.'/storage');
});

afterEach(function () {
    DB::purge('backup_test');
    File::deleteDirectory($this->directory);
});

test('the sqlite database is copied into the backups folder', function () {
    $this->artisan('app:backup-database --connection=backup_test')->assertSuccessful();

    $backups = File::glob(storage_path('app/backups/database-*.sqlite'));

    expect($backups)->toHaveCount(1);

    $copy = new PDO('sqlite:'.$backups[0]);
    expect($copy->query('select body from notes')->fetchColumn())->toBe('hello');
});

test('old backups beyond the retention are pruned', function () {
    File::ensureDirectoryExists(storage_path('app/backups'));

    foreach (['2026-01-01', '2026-01-02', '2026-01-03'] as $day) {
        File::put(storage_path("app/backups/database-{$day}-000000.sqlite"), 'old');
    }

    $this->artisan('app:backup-database --keep=2 --connection=backup_test')->assertSuccessful();

    $names = array_map('basename', File::glob(storage_path('app/backups/database-*.sqlite')));

    expect($names)->toHaveCount(2)
        ->and($names)->not->toContain('database-2026-01-01-000000.sqlite')
        ->and($names)->not->toContain('database-2026-01-02-000000.sqlite');
});

test('non sqlite connections are skipped with a warning', function () {
    config([
        'database.connections.pretend_mysql' => ['driver' => 'mysql'],
    ]);

    $this->artisan('app:backup-database --connection=pretend_mysql')->assertSuccessful()->expectsOutputToContain('Only SQLite');
});

test('a missing database file fails', function () {
    config(['database.connections.backup_test.database' => $this->directory.'/missing.sqlite']);

    $this->artisan('app:backup-database --connection=backup_test')->assertFailed();
});

test('the backup is scheduled daily', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toContain('app:backup-database');
});
