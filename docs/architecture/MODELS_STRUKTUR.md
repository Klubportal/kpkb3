# 📁 KLUBPORTAL - MODELS STRUKTUR DOKUMENTATION

**Datum:** 25. Oktober 2025  
**Multi-Tenancy System:** Separate Datenbanken pro Tenant

---

## 🏗️ ÜBERSICHT

Das System verwendet **strikte Datenbank-Trennung**:
- **Central Models** → `klubportal_landlord` Datenbank
- **Tenant Models** → `tenant_testclub`, `tenant_xyz`, etc.

---

## 📂 1. CENTRAL MODELS

**Pfad:** `app/Models/Central/`  
**Datenbank:** `klubportal_landlord`  
**Zweck:** Superadmin-Verwaltung, Tenant-Management, zentrale Ressourcen

### ✅ Vorhandene Central Models:

#### 1️⃣ User.php (Superadmin User)
```php
<?php

namespace App\Models\Central;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;

    protected $connection = 'mysql';  // ✅ Central DB Connection
    protected $table = 'users';
    protected $guard_name = 'web';    // ✅ Web Guard für Central

    protected $fillable = ['name', 'email', 'password'];
}
```

**Verwendung:**
- Superadmin-Accounts
- Central Panel Login (`localhost:8000/admin`)
- Tenant-Verwaltung

---

#### 2️⃣ Tenant.php (Vereine/Clubs)
```php
<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $connection = 'central';  // ✅ Explizit Central
    
    // Automatische DB-Erstellung: tenant_testclub, tenant_xyz
}
```

**Verwendung:**
- Verwaltung aller Vereine
- Automatische DB-Erstellung
- Domain-Verwaltung (testclub.localhost)

---

#### 3️⃣ Plan.php (Preispläne)
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'central';
    
    protected $fillable = [
        'name',
        'price',
        'features',
        'max_members',
        'max_teams',
    ];
}
```

**Verwendung:**
- SaaS-Preispläne (Basic, Pro, Enterprise)
- Feature-Limits pro Plan
- Abonnement-Verwaltung

---

#### 4️⃣ News.php (Zentrale News)
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $connection = 'central';
    
    protected $fillable = [
        'title',
        'content',
        'published_at',
    ];
}
```

**Verwendung:**
- Globale Klubportal-News
- System-Ankündigungen
- Updates für alle Tenants

---

#### 5️⃣ Page.php (Zentrale Seiten)
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $connection = 'central';
    
    protected $fillable = [
        'title',
        'slug',
        'content',
    ];
}
```

**Verwendung:**
- Zentrale CMS-Seiten
- Über uns, Kontakt, AGB, etc.

---

### 🔮 EMPFOHLENE ZUSÄTZLICHE CENTRAL MODELS:

#### Subscription.php (Zahlungen)
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $connection = 'central';
    
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',           // active, canceled, expired
        'starts_at',
        'ends_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
```

**Migration erstellen:**
```bash
php artisan make:migration create_subscriptions_table
```

---

## 📂 2. TENANT MODELS

**Pfad:** `app/Models/Tenant/`  
**Datenbanken:** `tenant_testclub`, `tenant_xyz`, etc.  
**Zweck:** Club-spezifische Daten (isoliert pro Verein)

### ✅ Vorhandene Tenant Models:

#### 1️⃣ User.php (Club-Mitglieder)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // ✅ KEINE $connection angeben!
    // Stancl/Tenancy managed DB automatisch
    
    protected $table = 'users';
    protected $guard_name = 'tenant';  // ✅ Tenant Guard

    protected $fillable = ['name', 'email', 'password'];
}
```

**Verwendung:**
- Club-Admin, Trainer, Mitglieder
- Tenant Panel Login (`testclub.localhost:8000/club`)
- Isoliert pro Verein

---

#### 2️⃣ Player.php (Spieler)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Player extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    // ✅ KEINE $connection - Tenancy managed automatisch
    
    protected $fillable = [
        'team_id',
        'user_id',
        'first_name',
        'last_name',
        'birth_date',
        'position',
        'jersey_number',
        'nationality',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
```

---

#### 3️⃣ Team.php (Mannschaften)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'season_id',
        'name',
        'age_group',
        'gender',
        'league',
    ];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
```

---

#### 4️⃣ FootballMatch.php (Spiele)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    protected $table = 'matches';  // football_matches oder matches
    
    protected $fillable = [
        'team_id',
        'opponent',
        'match_date',
        'location',
        'home_score',
        'away_score',
        'is_home',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'is_home' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
```

---

#### 5️⃣ News.php (Club News)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

---

#### 6️⃣ Event.php (Veranstaltungen)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'location',
        'max_participants',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];
}
```

---

#### 7️⃣ Member.php (Vereinsmitglieder)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'membership_number',
        'joined_at',
        'status',  // active, inactive, suspended
        'membership_type',  // full, youth, honorary
    ];

    protected $casts = [
        'joined_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

#### 8️⃣ Season.php (Saisons)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'name',         // 2024/2025
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
```

---

#### 9️⃣ Training.php (Trainingseinheiten)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'team_id',
        'training_date',
        'location',
        'notes',
    ];

    protected $casts = [
        'training_date' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
```

---

#### 🔟 Page.php (Club-Seiten)
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'order',
        'show_in_menu',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true)
                    ->where('is_published', true)
                    ->orderBy('order');
    }
}
```

---

## 🔑 WICHTIGE REGELN

### ✅ DO's (Richtig):

#### Central Models:
```php
namespace App\Models\Central;

class User extends Authenticatable
{
    protected $connection = 'central';  // ✅ Explizit angeben
    protected $guard_name = 'web';      // ✅ Web Guard
}
```

#### Tenant Models:
```php
namespace App\Models\Tenant;

class Player extends Model
{
    // ✅ KEINE $connection angeben
    // ✅ Tenancy Middleware managed automatisch
    
    protected $fillable = ['name', 'position'];
}
```

---

### ❌ DON'Ts (Falsch):

```php
// ❌ FALSCH: Tenant Model mit Connection
namespace App\Models\Tenant;

class Player extends Model
{
    protected $connection = 'tenant';  // ❌ NICHT setzen!
}
```

```php
// ❌ FALSCH: Central Model ohne Connection
namespace App\Models\Central;

class User extends Authenticatable
{
    // ❌ Fehlt: protected $connection = 'central';
}
```

---

## 🔄 WIE TENANCY FUNKTIONIERT

### 1️⃣ Central Context (localhost:8000/admin):
```php
// Automatisch auf 'central' Connection
$users = \App\Models\Central\User::all();  
// SQL: SELECT * FROM klubportal_landlord.users
```

### 2️⃣ Tenant Context (testclub.localhost:8000/club):
```php
// Middleware initialisiert Tenant
// Database wird zu tenant_testclub gewechselt

$players = \App\Models\Tenant\Player::all();
// SQL: SELECT * FROM tenant_testclub.players
```

### 3️⃣ Manueller Kontext-Wechsel:
```php
use App\Models\Central\Tenant;

// Tenant laden
$tenant = Tenant::find('testclub');

// In Tenant-Context wechseln
tenancy()->initialize($tenant);

// Jetzt greifen Tenant Models auf tenant_testclub zu
$players = \App\Models\Tenant\Player::all();

// Zurück zu Central
tenancy()->end();
```

---

## 📊 ÜBERSICHT TABELLE

| Model | Namespace | Connection | Guard | Datenbank |
|-------|-----------|------------|-------|-----------|
| **Central\User** | `App\Models\Central` | `central` | `web` | `klubportal_landlord` |
| **Central\Tenant** | `App\Models\Central` | `central` | - | `klubportal_landlord` |
| **Central\Plan** | `App\Models\Central` | `central` | - | `klubportal_landlord` |
| **Central\News** | `App\Models\Central` | `central` | - | `klubportal_landlord` |
| **Central\Page** | `App\Models\Central` | `central` | - | `klubportal_landlord` |
| **Tenant\User** | `App\Models\Tenant` | *auto* | `tenant` | `tenant_testclub` |
| **Tenant\Player** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Team** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\FootballMatch** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\News** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Event** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Member** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Season** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Training** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |
| **Tenant\Page** | `App\Models\Tenant` | *auto* | - | `tenant_testclub` |

---

## 🛠️ NEUE MODELS ERSTELLEN

### Central Model erstellen:
```bash
# Model erstellen
php artisan make:model Models/Central/Subscription

# Mit Migration
php artisan make:model Models/Central/Subscription -m

# Migration läuft auf Central DB
php artisan migrate --database=central
```

**Beispiel Migration:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('central')->create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->string('status');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }
};
```

---

### Tenant Model erstellen:
```bash
# Model erstellen
php artisan make:model Models/Tenant/Competition

# Mit Migration
php artisan make:model Models/Tenant/Competition -m

# Migration in tenant/ Ordner verschieben
Move-Item database/migrations/2025_XX_XX_create_competitions_table.php database/migrations/tenant/

# Auf allen Tenants ausführen
php artisan tenants:migrate
```

**Beispiel Migration:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('season');
            $table->string('league_level');
            $table->timestamps();
        });
    }
};
```

---

## ✅ ZUSAMMENFASSUNG

### Central Models (`app/Models/Central/`):
- ✅ Explizite Connection: `protected $connection = 'central';`
- ✅ Für Superadmin-Verwaltung
- ✅ Shared Data über alle Tenants
- ✅ Migrations: `php artisan migrate --database=central`

### Tenant Models (`app/Models/Tenant/`):
- ✅ **KEINE** Connection angeben
- ✅ Tenancy Middleware managed automatisch
- ✅ Isoliert pro Verein
- ✅ Migrations: `php artisan tenants:migrate`

**Status:** ✅ Deine Struktur ist bereits perfekt implementiert!

---

**📅 Erstellt:** 25. Oktober 2025  
**🔄 Letztes Update:** Nach Konfigurationsanpassungen  
**✅ Status:** Models korrekt strukturiert
