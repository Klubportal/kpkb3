# Multi-Tenant Dynamisches Menüsystem

## Übersicht

Das dynamische Menüsystem ermöglicht es jedem Tenant (Verein), sein eigenes Menü im Club-Panel zu verwalten. Menüpunkte werden in der Tenant-Datenbank gespeichert und können über ein Admin-Interface angepasst werden.

## Architektur

### Datenbank-Struktur

**Tabelle: `tenant_menu_items`** (pro Tenant-Datenbank)

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `id` | BIGINT | Primärschlüssel |
| `label` | VARCHAR | Menü-Text (z.B. "Spieler") |
| `icon` | VARCHAR | Heroicon Name (z.B. "heroicon-o-users") |
| `url` | VARCHAR | Direkte URL (optional) |
| `route` | VARCHAR | Laravel Route Name (optional) |
| `route_parameters` | JSON | Parameter für Route |
| `sort_order` | INTEGER | Sortierreihenfolge (aufsteigend) |
| `is_active` | BOOLEAN | Sichtbarkeit ein/aus |
| `group` | VARCHAR | Gruppierung (z.B. "Content", "Verein") |
| `badge` | VARCHAR | Badge-Text (z.B. "Neu", "5") |
| `badge_color` | VARCHAR | Badge-Farbe |
| `permissions` | JSON | Erforderliche Permissions |
| `roles` | JSON | Erforderliche Rollen |
| `parent_id` | BIGINT | Parent für Submenüs (NULL = Root) |
| `created_at` | TIMESTAMP | Erstellungsdatum |
| `updated_at` | TIMESTAMP | Änderungsdatum |

### Komponenten

#### 1. Model: `App\Models\Tenant\TenantMenuItem`

**Methoden:**
- `parent()` - BelongsTo Relation zu Parent-Menü
- `children()` - HasMany Relation zu Submenüs
- `scopeActive()` - Nur aktive Menüpunkte
- `scopeRoot()` - Nur Root-Menüpunkte (kein Parent)
- `scopeOrdered()` - Sortiert nach `sort_order`
- `canView($user)` - Prüft Permissions/Rollen
- `getUrl()` - Generiert URL (aus `url` oder `route`)

#### 2. Service: `App\Services\TenantMenuService`

**Methoden:**
- `getMenuItems($user)` - Alle sichtbaren Menüpunkte für User
- `getFilamentMenuItems($user)` - Konvertiert zu Filament MenuItem[]
- `convertToFilamentMenuItem($item)` - Konvertiert einzelnes Item
- `createDefaultMenuItems()` - Erstellt Standard-Menü für neuen Tenant
- `syncMenuItems($items)` - Import/Export von Menüs

#### 3. Integration: `TenantPanelProvider`

Das Menüsystem wird automatisch in jeden Tenant geladen:

```php
use App\Services\TenantMenuService;

// In boot() oder panel()
$menuService = app(TenantMenuService::class);
$menuItems = $menuService->getFilamentMenuItems();
```

## Standard-Menüpunkte

Nach dem Seeding hat jeder Tenant folgende Menüpunkte:

### Ungrouped
1. **Dashboard** (sort: 1) - /club/dashboard
10. **Einstellungen** (sort: 99) - /club/settings

### Content (sort: 10-19)
- **News** (sort: 10) - /club/news

### Verein (sort: 20-29)
- **Spieler** (sort: 20) - /club/players
- **Mannschaften** (sort: 21) - /club/teams
- **Mitglieder** (sort: 22) - /club/members

### Spielbetrieb (sort: 30-39)
- **Spiele** (sort: 30) - /club/matches
- **Training** (sort: 31) - /club/trainings
- **Events** (sort: 32) - /club/events

### Verwaltung (sort: 90-99)
- **Menü verwalten** (sort: 90) - /club/tenant-menu-items

## Verwendung

### Menüpunkte abrufen

```php
use App\Services\TenantMenuService;

$menuService = app(TenantMenuService::class);

// Alle Menüpunkte für aktuellen User
$items = $menuService->getMenuItems();

// Für Filament Panel
$filamentItems = $menuService->getFilamentMenuItems();
```

### Neuen Menüpunkt erstellen

```php
use App\Models\Tenant\TenantMenuItem;

TenantMenuItem::create([
    'label' => 'Finanzen',
    'icon' => 'heroicon-o-currency-dollar',
    'url' => '/club/finances',
    'sort_order' => 40,
    'is_active' => true,
    'group' => 'Verwaltung',
    'permissions' => ['view_finances'], // Optional
]);
```

### Permissions prüfen

```php
$menuItem = TenantMenuItem::find(1);

if ($menuItem->canView($user)) {
    // User kann diesen Menüpunkt sehen
}
```

### Submenü erstellen

```php
$parent = TenantMenuItem::create([
    'label' => 'Statistiken',
    'icon' => 'heroicon-o-chart-bar',
    'sort_order' => 35,
]);

TenantMenuItem::create([
    'label' => 'Tore',
    'url' => '/club/stats/goals',
    'parent_id' => $parent->id,
    'sort_order' => 1,
]);
```

## Migration

### Neue Tenants
Bei der Erstellung eines neuen Tenants:

```php
// In TenantCreated Event Listener
app(TenantMenuService::class)->createDefaultMenuItems();
```

### Bestehende Tenants
Migration ausführen:

```bash
php artisan tenants:migrate --path=database/migrations/2025_10_28_202000_create_tenant_menu_items_table.php
php artisan tenants:seed --class=TenantMenuSeeder
```

## Admin-Interface

TODO: Filament Resource erstellen für `TenantMenuItem` Management:

- **List:** Alle Menüpunkte mit Drag&Drop Sortierung
- **Create/Edit:** Formulare für Label, Icon, URL, Permissions, etc.
- **Groups:** Gruppenverwaltung
- **Preview:** Live-Vorschau des Menüs

## Vorteile

✅ **Tenant-spezifisch:** Jeder Verein kann sein Menü individuell anpassen
✅ **Permissions-basiert:** Menüpunkte nur für berechtigte User sichtbar
✅ **Flexibel:** URL oder Route, mit/ohne Parameter
✅ **Gruppierung:** Übersichtliche Organisation
✅ **Badges:** Dynamische Anzeige (z.B. "5 neue Nachrichten")
✅ **Submenüs:** Hierarchische Struktur möglich
✅ **Performance:** Optimierte Queries mit Eager Loading

## Nächste Schritte

1. ✅ Migration erstellt
2. ✅ Model & Service implementiert
3. ✅ Standard-Menüpunkte via Seeder
4. ⏳ Filament Resource für Menu-Management erstellen
5. ⏳ Integration in TenantPanelProvider finalisieren
6. ⏳ Frontend-Anzeige testen
