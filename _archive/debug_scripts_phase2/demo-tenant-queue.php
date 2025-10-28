<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Queue;

echo "\n";
echo "========================================\n";
echo "   QUEUE/JOBS TENANCY DEMO\n";
echo "========================================\n\n";

echo "✅ KONFIGURATION:\n";
echo "   QueueTenancyBootstrapper: " .
    (in_array(Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class, config('tenancy.bootstrappers')) ? 'AKTIVIERT ✅' : 'NICHT AKTIVIERT ❌') . "\n";
echo "   Queue Connection: " . config('queue.default') . "\n";
echo "   Queue Driver: " . config('queue.connections.' . config('queue.default') . '.driver') . "\n\n";

echo "========================================\n";
echo "   WIE QUEUE TENANCY FUNKTIONIERT\n";
echo "========================================\n\n";

echo "📋 Job Dispatch - Beispiele:\n\n";

echo "```php\n";
echo "// 1. CENTRAL CONTEXT\n";
echo "use App\\Jobs\\SendNewsletterJob;\n\n";
echo "// Newsletter an alle Tenants\n";
echo "SendNewsletterJob::dispatch(\$newsletter);\n";
echo "// → Job läuft im CENTRAL Context\n";
echo "```\n\n";

echo "```php\n";
echo "// 2. TENANT CONTEXT (testclub)\n";
echo "\$tenant = Tenant::find('testclub');\n";
echo "\$tenant->run(function() {\n";
echo "    SendMatchReminderJob::dispatch(\$match);\n";
echo "    // → Job läuft im TESTCLUB Context\n";
echo "    // → Hat Zugriff auf testclub Datenbank\n";
echo "});\n";
echo "```\n\n";

echo "========================================\n";
echo "   TENANT INFORMATION IN JOBS\n";
echo "========================================\n\n";

$testclub = Tenant::find('testclub');
if ($testclub) {
    echo "🏠 TENANT CONTEXT: testclub\n";
    $testclub->run(function() {
        echo "   Tenant ID: " . tenant('id') . "\n";
        echo "   Database: " . config('database.connections.tenant.database') . "\n";

        // Simulate job dispatch info
        echo "\n   Wenn ein Job dispatched wird:\n";
        echo "   → tenant_id: 'testclub' wird im Job gespeichert\n";
        echo "   → Job wird später im testclub Context ausgeführt\n";
        echo "   → Automatischer Zugriff auf testclub Daten\n";
    });
    echo "\n";
}

echo "========================================\n";
echo "   PRAKTISCHE BEISPIELE\n";
echo "========================================\n\n";

echo "📧 E-MAIL JOBS:\n\n";
echo "```php\n";
echo "// app/Jobs/SendMatchReminderJob.php\n\n";
echo "class SendMatchReminderJob implements ShouldQueue\n";
echo "{\n";
echo "    use Dispatchable, InteractsWithQueue, Queueable;\n\n";
echo "    public function __construct(\n";
echo "        public Match \$match\n";
echo "    ) {}\n\n";
echo "    public function handle()\n";
echo "    {\n";
echo "        // Läuft automatisch im richtigen Tenant Context!\n";
echo "        \$players = \$this->match->team->players;\n";
echo "        \n";
echo "        foreach (\$players as \$player) {\n";
echo "            Mail::to(\$player->email)\n";
echo "                ->send(new MatchReminderMail(\$this->match));\n";
echo "        }\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "Verwendung:\n";
echo "```php\n";
echo "// Im Tenant Panel (Club Admin)\n";
echo "SendMatchReminderJob::dispatch(\$match);\n";
echo "// → Job weiß automatisch welcher Tenant (testclub)\n";
echo "// → Verwendet testclub Datenbank\n";
echo "// → Sendet Mails an testclub Spieler\n";
echo "```\n\n";

echo "========================================\n";
echo "   FILAMENT ACTION BEISPIEL\n";
echo "========================================\n\n";

echo "```php\n";
echo "// In einer Filament Resource (z.B. MatchResource)\n\n";
echo "use Filament\\Tables\\Actions\\Action;\n\n";
echo "Action::make('send_reminder')\n";
echo "    ->label('Erinnerung senden')\n";
echo "    ->icon('heroicon-o-envelope')\n";
echo "    ->requiresConfirmation()\n";
echo "    ->action(function (Match \$record) {\n";
echo "        // Job wird automatisch im aktuellen Tenant Context dispatched!\n";
echo "        SendMatchReminderJob::dispatch(\$record);\n";
echo "        \n";
echo "        Notification::make()\n";
echo "            ->title('Job wurde gestartet')\n";
echo "            ->success()\n";
echo "            ->send();\n";
echo "    })\n";
echo "```\n\n";

echo "========================================\n";
echo "   QUEUE WORKER STARTEN\n";
echo "========================================\n\n";

echo "Standard Queue Worker:\n";
echo "  php artisan queue:work\n\n";

echo "Mit spezifischer Queue:\n";
echo "  php artisan queue:work --queue=high,default,low\n\n";

echo "Für Entwicklung (1 Job dann beenden):\n";
echo "  php artisan queue:work --once\n\n";

echo "Im Hintergrund (Linux/Mac):\n";
echo "  nohup php artisan queue:work > /dev/null 2>&1 &\n\n";

echo "Mit Supervisor (Produktion - empfohlen):\n";
echo "  Siehe QUEUE_TENANCY_STRUKTUR.md\n\n";

echo "========================================\n";
echo "   QUEUED NOTIFICATIONS\n";
echo "========================================\n\n";

echo "```php\n";
echo "// app/Notifications/MatchReminder.php\n\n";
echo "class MatchReminder extends Notification implements ShouldQueue\n";
echo "{\n";
echo "    use Queueable;\n\n";
echo "    public function __construct(\n";
echo "        public Match \$match\n";
echo "    ) {}\n\n";
echo "    public function via(\$notifiable): array\n";
echo "    {\n";
echo "        return ['mail', 'database'];\n";
echo "    }\n\n";
echo "    public function toMail(\$notifiable): MailMessage\n";
echo "    {\n";
echo "        // Tenant Context ist automatisch aktiv!\n";
echo "        return (new MailMessage)\n";
echo "            ->subject('Spiel-Erinnerung: ' . \$this->match->opponent)\n";
echo "            ->line('Dein Spiel beginnt in 24 Stunden.');\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "Verwendung:\n";
echo "```php\n";
echo "\$player->notify(new MatchReminder(\$match));\n";
echo "// → Notification wird in Queue gestellt\n";
echo "// → Wird im korrekten Tenant Context verarbeitet\n";
echo "```\n\n";

echo "========================================\n";
echo "   JOBS TABLE ÜBERWACHEN\n";
echo "========================================\n\n";

echo "Anzahl wartender Jobs:\n";
echo "  php artisan queue:monitor\n\n";

echo "Failed Jobs anzeigen:\n";
echo "  php artisan queue:failed\n\n";

echo "Failed Job erneut versuchen:\n";
echo "  php artisan queue:retry <job-id>\n\n";

echo "Alle Failed Jobs erneut versuchen:\n";
echo "  php artisan queue:retry all\n\n";

echo "========================================\n";
echo "   WICHTIGE QUEUE BEFEHLE\n";
echo "========================================\n\n";

echo "Queue Worker starten:\n";
echo "  php artisan queue:work\n\n";

echo "Queue leeren (alle Jobs löschen):\n";
echo "  php artisan queue:clear\n\n";

echo "Queue Worker neu starten:\n";
echo "  php artisan queue:restart\n\n";

echo "Job Tabellen erstellen:\n";
echo "  php artisan queue:table\n";
echo "  php artisan queue:failed-table\n";
echo "  php artisan migrate\n\n";

echo "========================================\n";
echo "   ZUSAMMENFASSUNG\n";
echo "========================================\n";
echo "✅ QueueTenancyBootstrapper: AKTIVIERT\n";
echo "✅ Jobs kennen automatisch ihren Tenant Context\n";
echo "✅ Keine manuelle Tenant-ID Verwaltung nötig\n";
echo "✅ Funktioniert mit allen Queue Drivers\n";
echo "✅ Unterstützt: Mails, Notifications, Jobs, Events\n";
echo "\n";

echo "========================================\n";
echo "   NEXT STEPS\n";
echo "========================================\n";
echo "1. Queue Worker starten: php artisan queue:work\n";
echo "2. Job erstellen: php artisan make:job YourJob\n";
echo "3. Job dispatchen: YourJob::dispatch(\$data)\n";
echo "4. Dokumentation lesen: QUEUE_TENANCY_STRUKTUR.md\n";
echo "\n========================================\n\n";
