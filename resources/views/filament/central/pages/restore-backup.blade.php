<x-filament-panels::page>
    @php
        $backups = [];
        $backupPath = base_path('backups/Klubportal');
        if (file_exists($backupPath)) {
            $files = glob($backupPath . '/*.zip');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'date' => filemtime($file),
                ];
            }
            usort($backups, function($a, $b) {
                return $b['date'] - $a['date'];
            });
        }
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Backup wiederherstellen
        </x-slot>

        <x-slot name="description">
            Verwenden Sie den Terminal-Befehl, um ein Backup wiederherzustellen
        </x-slot>

        <div class="space-y-4">
            <x-filament::section
                icon="heroicon-o-exclamation-triangle"
                icon-color="danger"
            >
                <x-slot name="heading">
                    Achtung: Datenüberschreibung!
                </x-slot>

                <div class="text-sm">
                    <p>Die Wiederherstellung eines Backups überschreibt <strong>ALLE</strong> aktuellen Daten in der Datenbank.</p>
                    <p class="mt-2"><strong>Erstellen Sie vor der Wiederherstellung unbedingt ein aktuelles Backup!</strong></p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Terminal-Befehl
                </x-slot>

                <div class="space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Um ein Backup wiederherzustellen, führen Sie folgenden Befehl im Terminal aus:
                    </p>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            value="php artisan backup:restore"
                            readonly
                            class="font-mono"
                        />
                    </x-filament::input.wrapper>

                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold mb-2">Schritte:</p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Öffnen Sie ein Terminal/PowerShell im Projektverzeichnis</li>
                            <li>Führen Sie den obigen Befehl aus</li>
                            <li>Wählen Sie das gewünschte Backup aus der Liste</li>
                            <li>Bestätigen Sie die Wiederherstellung</li>
                            <li>Warten Sie, bis der Vorgang abgeschlossen ist</li>
                            <li>Melden Sie sich erneut an</li>
                        </ol>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Verfügbare Backups ({{ count($backups) }})
        </x-slot>

        @if (count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">
                                Dateiname
                            </th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">
                                Erstellt am
                            </th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">
                                Größe
                            </th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">
                                Alter
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($backups as $backup)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $backup['name'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ date('d.m.Y H:i:s', $backup['date']) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($backup['size'] > 1048576)
                                        {{ round($backup['size'] / 1048576, 2) }} MB
                                    @else
                                        {{ round($backup['size'] / 1024, 2) }} KB
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @php
                                        $diff = time() - $backup['date'];
                                        $days = floor($diff / 86400);
                                        $hours = floor(($diff % 86400) / 3600);
                                        $minutes = floor(($diff % 3600) / 60);
                                    @endphp
                                    @if ($days > 0)
                                        {{ $days }} Tag{{ $days > 1 ? 'e' : '' }}
                                    @elseif ($hours > 0)
                                        {{ $hours }} Stunde{{ $hours > 1 ? 'n' : '' }}
                                    @else
                                        {{ $minutes }} Minute{{ $minutes > 1 ? 'n' : '' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12">
                <x-filament::icon
                    icon="heroicon-o-archive-box-x-mark"
                    class="h-12 w-12 text-gray-400 dark:text-gray-500"
                />
                <h3 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">
                    Keine Backups vorhanden
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Erstellen Sie zuerst ein Backup.
                </p>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section
        icon="heroicon-o-information-circle"
        icon-color="info"
        class="mt-6"
    >
        <x-slot name="heading">
            Hinweise zur Wiederherstellung
        </x-slot>

        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
            <li>Die Wiederherstellung kann einige Minuten dauern</li>
            <li>Sie werden nach der Wiederherstellung automatisch abgemeldet</li>
            <li>Melden Sie sich danach erneut an</li>
            <li>Prüfen Sie nach der Anmeldung, ob alle Daten korrekt wiederhergestellt wurden</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
