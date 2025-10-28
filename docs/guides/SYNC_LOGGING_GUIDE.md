# Sync Logging System

Dokumentation für das erweiterte Sync-Logging-System mit detaillierter Statistik-Verfolgung.

## Übersicht

Alle Sync-Commands loggen jetzt automatisch ihre Aktivitäten in der `sync_logs` Tabelle. Das System erfasst:

- **Records**: Inserted, Updated, Skipped, Failed, Total
- **Duration**: Dauer in Sekunden
- **Status**: success, partial, failed, running
- **Metadata**: JSON mit zusätzlichen Informationen
- **Errors**: Fehlermeldungen und Stack Traces

## Datenbank Schema

### sync_logs Tabelle

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| id | bigint | Auto-increment Primary Key |
| tenant_id | varchar(191) | Tenant-ID oder 'landlord' |
| sync_type | varchar(50) | Art des Syncs (comet_matches, comet_rankings, etc.) |
| status | enum | success, failed, partial, running |
| records_processed | int | Anzahl verarbeiteter Datensätze (inserted + updated) |
| records_inserted | int | Anzahl neu eingefügter Datensätze |
| records_updated | int | Anzahl aktualisierter Datensätze |
| records_skipped | int | **NEU**: Anzahl übersprungener Datensätze (keine Änderung) |
| records_failed | int | Anzahl fehlgeschlagener Datensätze |
| total_records | int | **NEU**: Gesamtanzahl aller Datensätze |
| started_at | datetime | Startzeitpunkt |
| completed_at | datetime | Endzeitpunkt (null wenn running) |
| duration_seconds | int | **NEU**: Dauer in Sekunden |
| error_message | text | Fehlermeldung (wenn status=failed) |
| error_details | text | **NEU**: Stack Trace oder detaillierte Fehlerinfo |
| sync_params | longtext | JSON mit Sync-Parametern |
| sync_metadata | longtext | **NEU**: JSON mit zusätzlichen Metadaten |

## SyncLog Model

### Verwendung

```php
use App\Models\SyncLog;

// Sync starten
$syncLog = SyncLog::startSync('comet_matches', 'landlord', [
    'competition_id' => 123
]);

try {
    // ... Sync-Logik ...
    
    // Sync erfolgreich abschließen
    $syncLog->complete([
        'inserted' => 10,
        'updated' => 5,
        'skipped' => 100,
        'failed' => 0,
        'total' => 115,
        'status' => 'success',
        'metadata' => [
            'competitions_processed' => 11,
            'api_calls' => 50,
        ],
    ]);
    
} catch (\Exception $e) {
    // Sync als fehlgeschlagen markieren
    $syncLog->fail($e->getMessage(), $e->getTraceAsString());
}
```

### Methoden

#### startSync(string $syncType, ?string $tenantId = null, ?array $params = []): SyncLog

Startet einen neuen Sync-Log-Eintrag.

**Parameter:**
- `$syncType`: Art des Syncs (z.B. 'comet_matches', 'tenant_sync')
- `$tenantId`: Tenant-ID oder 'landlord' (Standard: 'landlord')
- `$params`: Optional, Array mit Sync-Parametern

**Rückgabe:** SyncLog Instanz

#### complete(array $stats = []): void

Schließt einen Sync ab und speichert Statistiken.

**Parameter `$stats` Array:**
- `inserted`: Anzahl eingefügte Datensätze
- `updated`: Anzahl aktualisierte Datensätze
- `skipped`: Anzahl übersprungene Datensätze
- `failed`: Anzahl fehlgeschlagene Datensätze
- `total`: Gesamtanzahl verarbeitete Datensätze
- `status`: 'success', 'partial' oder 'failed'
- `error`: Optional, Fehlermeldung
- `error_details`: Optional, detaillierte Fehlerinfo
- `metadata`: Optional, zusätzliche Metadaten als Array

#### fail(string $error, ?string $errorDetails = null): void

Markiert einen Sync als fehlgeschlagen.

**Parameter:**
- `$error`: Fehlermeldung
- `$errorDetails`: Optional, Stack Trace oder Details

## Integrierte Commands

Alle folgenden Commands loggen automatisch:

### 1. comet:sync-matches

**Sync-Type:** `comet_matches`

**Statistiken:**
- inserted, updated, skipped: Anzahl Matches
- total: Gesamtanzahl Matches
- failed: Fehler beim Sync

**Metadata:**
```json
{
  "competitions_processed": 11
}
```

### 2. comet:sync-rankings

**Sync-Type:** `comet_rankings`

**Statistiken:**
- inserted, updated, skipped: Anzahl Rankings
- total: Gesamtanzahl Rankings
- failed: Fehler beim Sync

**Metadata:**
```json
{
  "competitions_processed": 11
}
```

### 3. comet:sync-topscorers

**Sync-Type:** `comet_top_scorers`

**Statistiken:**
- inserted, updated, skipped: Anzahl Top Scorers
- total: Gesamtanzahl Top Scorers
- failed: Fehler beim Sync

**Metadata:**
```json
{
  "competitions_processed": 11
}
```

### 4. comet:sync-all

**Sync-Type:** `comet_all`

Master-Command, der alle drei obigen Commands ausführt.

**Metadata:**
```json
{
  "duration": 43.16,
  "syncs": ["matches", "rankings", "top_scorers"]
}
```

### 5. tenant:sync-comet

**Sync-Type:** `tenant_sync`

**Statistiken:**
- total: Anzahl Tenants
- failed: Anzahl fehlgeschlagene Tenants

**Metadata:**
```json
{
  "tenants_synced": 5,
  "total_matches": 500,
  "total_rankings": 100,
  "total_scorers": 200
}
```

## SQL Abfragen

### Letzte 10 Syncs anzeigen

```sql
SELECT 
    id,
    sync_type,
    status,
    records_inserted,
    records_updated,
    records_skipped,
    records_failed,
    total_records,
    duration_seconds,
    started_at,
    completed_at
FROM sync_logs
ORDER BY id DESC
LIMIT 10;
```

### Fehlgeschlagene Syncs

```sql
SELECT 
    id,
    sync_type,
    error_message,
    error_details,
    started_at
FROM sync_logs
WHERE status = 'failed'
ORDER BY started_at DESC;
```

### Sync-Performance pro Typ

```sql
SELECT 
    sync_type,
    COUNT(*) as total_runs,
    AVG(duration_seconds) as avg_duration,
    MAX(duration_seconds) as max_duration,
    MIN(duration_seconds) as min_duration,
    SUM(records_processed) as total_processed
FROM sync_logs
WHERE status = 'success'
GROUP BY sync_type
ORDER BY avg_duration DESC;
```

### Heute durchgeführte Syncs

```sql
SELECT 
    sync_type,
    COUNT(*) as runs,
    SUM(records_inserted) as total_inserted,
    SUM(records_updated) as total_updated,
    SUM(records_skipped) as total_skipped
FROM sync_logs
WHERE DATE(started_at) = CURDATE()
GROUP BY sync_type;
```

### Metadata auswerten

```sql
SELECT 
    id,
    sync_type,
    JSON_EXTRACT(sync_metadata, '$.competitions_processed') as competitions,
    JSON_EXTRACT(sync_metadata, '$.duration') as duration,
    started_at
FROM sync_logs
WHERE sync_metadata IS NOT NULL
ORDER BY id DESC
LIMIT 10;
```

## Scheduler Integration

Der Scheduler führt automatisch Syncs durch und loggt dabei:

```php
// routes/console.php

// Comet Sync alle 5 Minuten
Schedule::command('comet:sync-all')
    ->everyFiveMinutes()
    ->timezone('Europe/Zagreb');

// Tenant Sync alle 10 Minuten
Schedule::command('tenant:sync-comet --all')
    ->everyTenMinutes()
    ->timezone('Europe/Zagreb');
```

Jeder Scheduler-Lauf wird in `sync_logs` protokolliert.

## Monitoring

### Aktive Syncs prüfen

```sql
SELECT * FROM sync_logs WHERE status = 'running';
```

Wenn ein Sync länger als erwartet läuft, deutet dies auf ein Problem hin.

### Sync-Fehlerrate

```sql
SELECT 
    sync_type,
    COUNT(*) as total_runs,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_runs,
    ROUND(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as error_rate_percent
FROM sync_logs
GROUP BY sync_type;
```

### Durchschnittliche Records pro Sync

```sql
SELECT 
    sync_type,
    AVG(total_records) as avg_total,
    AVG(records_inserted) as avg_inserted,
    AVG(records_updated) as avg_updated,
    AVG(records_skipped) as avg_skipped
FROM sync_logs
WHERE status = 'success'
GROUP BY sync_type;
```

## Best Practices

1. **Regelmäßig prüfen**: Kontrolliere `sync_logs` regelmäßig auf fehlgeschlagene Syncs
2. **Performance überwachen**: Achte auf steigende `duration_seconds` Werte
3. **Metadata nutzen**: Speichere wichtige Kontext-Informationen in `sync_metadata`
4. **Alte Logs archivieren**: Nach einigen Monaten sollten alte Logs archiviert werden
5. **Fehler analysieren**: Nutze `error_details` für Stack Traces zur Fehlersuche

## Zukünftige Erweiterungen

- **Filament Resource**: Admin-UI zum Durchsuchen von Sync-Logs
- **E-Mail Alerts**: Bei fehlgeschlagenen Syncs automatisch benachrichtigen
- **Charts/Dashboards**: Visualisierung von Sync-Performance über Zeit
- **Log Rotation**: Automatisches Archivieren/Löschen alter Logs
- **Webhook Integration**: Bei wichtigen Events externe Systeme benachrichtigen

## Changelog

### 2025-10-26 - Initial Release

- Migration erstellt mit 4 neuen Feldern:
  - `records_skipped`
  - `total_records`
  - `error_details`
  - `sync_metadata`
- SyncLog Model mit Methoden `startSync()`, `complete()`, `fail()`
- Integration in alle 5 Sync-Commands
- Automatisches Logging bei jedem Scheduler-Lauf

---

**Autor:** GitHub Copilot  
**Datum:** 26. Oktober 2025  
**Version:** 1.0
