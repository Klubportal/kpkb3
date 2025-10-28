<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RestoreBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stellt ein Backup wieder her';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupFile = $this->argument('file');

        // Liste verfügbare Backups
        if (!$backupFile) {
            $backups = $this->getAvailableBackups();

            if (empty($backups)) {
                $this->error('Keine Backups gefunden!');
                return 1;
            }

            $this->info('Verfügbare Backups:');
            $this->table(
                ['#', 'Datei', 'Größe', 'Erstellt am'],
                array_map(function($key, $backup) {
                    return [
                        $key + 1,
                        $backup['name'],
                        $backup['size'],
                        $backup['date']
                    ];
                }, array_keys($backups), $backups)
            );

            $choice = $this->ask('Welches Backup wiederherstellen? (Nummer eingeben)');

            if (!isset($backups[$choice - 1])) {
                $this->error('Ungültige Auswahl!');
                return 1;
            }

            $backupFile = $backups[$choice - 1]['path'];
        }

        // Sicherheitsabfrage
        if (!$this->confirm('WARNUNG: Diese Aktion überschreibt alle aktuellen Daten! Fortfahren?', false)) {
            $this->info('Abgebrochen.');
            return 0;
        }

        $this->info('Starte Wiederherstellung...');

        try {
            $this->restoreBackup($backupFile);
            $this->info('✓ Backup erfolgreich wiederhergestellt!');
            $this->warn('Bitte neu einloggen.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Fehler: ' . $e->getMessage());
            return 1;
        }
    }

    protected function getAvailableBackups(): array
    {
        $backups = [];
        $backupName = config('backup.backup.name');

        foreach (config('backup.backup.destination.disks') as $diskName) {
            try {
                $disk = Storage::disk($diskName);

                if (!$disk->exists($backupName)) {
                    continue;
                }

                $files = $disk->files($backupName);

                foreach ($files as $file) {
                    if (str_ends_with($file, '.zip')) {
                        $backups[] = [
                            'name' => basename($file),
                            'path' => storage_path('app/' . $file),
                            'size' => $this->formatBytes($disk->size($file)),
                            'date' => date('d.m.Y H:i', $disk->lastModified($file))
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sortiere nach Datum absteigend
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return $backups;
    }

    protected function restoreBackup(string $backupPath): void
    {
        if (!file_exists($backupPath)) {
            throw new \Exception('Backup-Datei nicht gefunden: ' . $backupPath);
        }

        $tempPath = storage_path('app/temp-restore');

        // Bereinige temporäres Verzeichnis
        if (File::exists($tempPath)) {
            File::deleteDirectory($tempPath);
        }

        File::makeDirectory($tempPath, 0755, true);

        // Extrahiere ZIP
        $this->info('Extrahiere Backup...');
        $zip = new ZipArchive();

        if ($zip->open($backupPath) !== true) {
            throw new \Exception('ZIP-Datei konnte nicht geöffnet werden.');
        }

        $extractPath = $tempPath . '/extracted';
        File::makeDirectory($extractPath, 0755, true);
        $zip->extractTo($extractPath);
        $zip->close();

        // Finde SQL-Datei
        $this->info('Suche Datenbank-Dump...');
        $sqlFiles = File::glob($extractPath . '/db-dumps/*.sql');

        if (empty($sqlFiles)) {
            throw new \Exception('Keine SQL-Datei im Backup gefunden.');
        }

        $sqlFile = $sqlFiles[0];
        $this->info('Gefunden: ' . basename($sqlFile));

        // Importiere Datenbank
        $this->info('Importiere Datenbank...');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Windows-kompatibel
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < "%s"',
            $host,
            $username,
            $password,
            $database,
            $sqlFile
        );

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('MySQL Import fehlgeschlagen: ' . implode("\n", $output));
        }

        // Aufräumen
        File::deleteDirectory($tempPath);

        $this->info('Temporäre Dateien bereinigt.');
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
