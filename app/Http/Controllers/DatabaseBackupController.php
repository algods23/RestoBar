<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    public function store(): RedirectResponse
    {
        if (config('database.default') !== 'sqlite') {
            return back()->withErrors(['backup' => 'Database backup is only available for SQLite.']);
        }

        $databasePath = database_path('database.sqlite');
        $configuredPath = config('database.connections.sqlite.database');
        if (is_string($configuredPath) && $configuredPath !== '') {
            $databasePath = $configuredPath;
        }

        if (! File::exists($databasePath)) {
            return back()->withErrors(['backup' => 'SQLite database file was not found.']);
        }

        $backupDir = config('desktop.backup_dir', base_path('backup'));
        File::ensureDirectoryExists($backupDir);

        $backupPath = $backupDir . DIRECTORY_SEPARATOR . 'database-' . now()->format('Ymd-His') . '.sqlite';
        File::copy($databasePath, $backupPath);

        return back()->with('success', 'Database backup created: ' . $backupPath);
    }
}
