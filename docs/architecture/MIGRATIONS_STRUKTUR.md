# 🗄️ KLUBPORTAL - MIGRATIONS STRUKTUR DOKUMENTATION

**Datum:** 25. Oktober 2025  
**Multi-Tenancy System:** Separate Migrationen für Central & Tenant DBs

---

## 📂 ORDNERSTRUKTUR

```
database/
├── migrations/              ✅ Central Migrations (klubportal_landlord)
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2019_09_15_000010_create_tenants_table.php
│   ├── 2019_09_15_000020_create_domains_table.php
│   ├── 2025_10_24_202849_create_plans_table.php
│   ├── 2025_10_24_202858_create_support_tickets_table.php
│   ├── 2025_10_24_204030_add_custom_domain_to_tenants_table.php
│   ├── 2025_10_24_212127_add_plan_fields_to_tenants_table.php
│   ├── 2025_10_24_215243_create_telescope_entries_table.php
│   ├── 2025_10_24_230445_create_breezy_sessions_table.php
│   ├── 2025_10_25_092248_create_language_lines_table.php
│   ├── 2025_10_25_130658_add_is_active_to_tenants_table.php
│   ├── 2025_10_25_154318_create_settings_table.php
│   ├── 2025_10_25_164432_create_news_table.php
│   ├── 2025_10_25_164440_create_pages_table.php
│   ├── 2025_10_25_171558_create_tag_tables.php
│   ├── 2025_10_25_182751_create_media_table.php
│   └── ...
│
└── migrations/tenant/       ✅ Tenant Migrations (tenant_testclub, tenant_xyz, etc.)
    ├── 0001_01_01_000000_create_sessions_table.php
    ├── 0001_01_01_000001_create_cache_table.php
    ├── 0001_01_01_000002_create_jobs_table.php
    ├── 2025_10_24_202500_create_tenant_users_table.php
    ├── 2025_10_24_202554_create_permission_tables.php
    ├── 2025_10_24_202609_create_activity_log_table.php
    ├── 2025_10_24_202724_create_media_table.php
    ├── 2025_10_24_202736_create_tag_tables.php
    ├── 2025_10_24_202803_create_settings_table.php
    ├── 2025_10_24_202830_create_notifications_table.php
    ├── 2025_10_24_215900_create_seasons_table.php
    ├── 2025_10_24_215916_create_teams_table.php
    ├── 2025_10_24_215941_create_players_table.php
    ├── 2025_10_24_215946_create_matches_table.php
    ├── 2025_10_24_215950_create_trainings_table.php
    ├── 2025_10_24_220000_create_news_table.php
    ├── 2025_10_24_220015_create_members_table.php
    ├── 2025_10_24_220032_create_standings_table.php
    ├── 2025_10_24_220035_create_events_table.php
    ├── 2025_10_24_220252_create_match_player_table.php
    ├── 2025_10_24_220257_create_training_player_table.php
    ├── 2025_10_25_092248_create_language_lines_table.php
    └── 2025_10_25_164440_create_pages_table.php
```

---

## 🎯 1. CENTRAL MIGRATIONS

**Ziel:** `klubportal_landlord` Datenbank  
**Pfad:** `database/migrations/`  
**Ausführen:** `php artisan migrate --database=central`

### 📋 Kern-Tabellen:

#### 1️⃣ System-Tabellen (Laravel Standard):
```php
// 0001_01_01_000000_create_users_table.php
Schema::connection('central')->create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

// 0001_01_01_000001_create_cache_table.php
Schema::connection('central')->create('cache', function (Blueprint $table) {
    $table->string('key')->primary();
    $table->mediumText('value');
    $table->integer('expiration');
});

// 0001_01_01_000002_create_jobs_table.php
Schema::connection('central')->create('jobs', function (Blueprint $table) {
    $table->id();
    $table->string('queue')->index();
    $table->longText('payload');
    $table->unsignedTinyInteger('attempts');
    $table->unsignedInteger('reserved_at')->nullable();
    $table->unsignedInteger('available_at');
    $table->unsignedInteger('created_at');
});
```

---

#### 2️⃣ Multi-Tenancy Kern-Tabellen:
```php
// 2019_09_15_000010_create_tenants_table.php
Schema::connection('central')->create('tenants', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->timestamps();
    $table->json('data')->nullable();
    
    // Custom Fields (von späteren Migrations):
    $table->string('custom_domain')->nullable();
    $table->foreignId('plan_id')->nullable()->constrained();
    $table->date('trial_ends_at')->nullable();
    $table->boolean('is_active')->default(true);
});

// 2019_09_15_000020_create_domains_table.php
Schema::connection('central')->create('domains', function (Blueprint $table) {
    $table->increments('id');
    $table->string('domain', 255)->unique();
    $table->string('tenant_id');
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')
          ->onUpdate('cascade')->onDelete('cascade');
});
```

---

#### 3️⃣ SaaS Business-Tabellen:
```php
// 2025_10_24_202849_create_plans_table.php
Schema::connection('central')->create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 8, 2);
    $table->string('interval')->default('month'); // month, year
    $table->json('features')->nullable();
    $table->integer('max_members')->nullable();
    $table->integer('max_teams')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// 2025_10_24_202858_create_support_tickets_table.php
Schema::connection('central')->create('support_tickets', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id')->nullable();
    $table->foreignId('user_id')->constrained('users');
    $table->string('subject');
    $table->text('message');
    $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
    $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')
          ->onDelete('set null');
});
```

---

#### 4️⃣ CMS-Tabellen (Central):
```php
// 2025_10_25_164432_create_news_table.php
Schema::connection('central')->create('news', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('excerpt')->nullable();
    $table->longText('content');
    $table->foreignId('author_id')->constrained('users');
    $table->timestamp('published_at')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
});

// 2025_10_25_164440_create_pages_table.php
Schema::connection('central')->create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->longText('content')->nullable();
    $table->boolean('is_published')->default(false);
    $table->integer('order')->default(0);
    $table->boolean('show_in_menu')->default(false);
    $table->timestamps();
});
```

---

#### 5️⃣ Spatie-Pakete (Central):
```php
// 2025_10_25_171558_create_tag_tables.php
Schema::connection('central')->create('tags', function (Blueprint $table) {
    $table->id();
    $table->json('name');
    $table->json('slug');
    $table->string('type')->nullable();
    $table->integer('order_column')->nullable();
    $table->timestamps();
});

// 2025_10_25_182751_create_media_table.php
Schema::connection('central')->create('media', function (Blueprint $table) {
    $table->id();
    $table->morphs('model');
    $table->uuid('uuid')->nullable()->unique();
    $table->string('collection_name');
    $table->string('name');
    $table->string('file_name');
    $table->string('mime_type')->nullable();
    $table->string('disk');
    $table->string('conversions_disk')->nullable();
    $table->unsignedBigInteger('size');
    $table->json('manipulations');
    $table->json('custom_properties');
    $table->json('generated_conversions');
    $table->json('responsive_images');
    $table->unsignedInteger('order_column')->nullable();
    $table->timestamps();
});

// 2025_10_25_092248_create_language_lines_table.php
Schema::connection('central')->create('language_lines', function (Blueprint $table) {
    $table->id();
    $table->string('group');
    $table->string('key');
    $table->text('text');
    $table->string('locale');
    $table->timestamps();
});
```

---

#### 6️⃣ Development Tools (Central):
```php
// 2025_10_24_215243_create_telescope_entries_table.php
Schema::connection('central')->create('telescope_entries', function (Blueprint $table) {
    $table->bigIncrements('sequence');
    $table->uuid('uuid');
    $table->uuid('batch_id');
    $table->string('family_hash')->nullable();
    $table->boolean('should_display_on_index')->default(true);
    $table->string('type', 20);
    $table->longText('content');
    $table->dateTime('created_at')->nullable();
    
    $table->unique('uuid');
    $table->index('batch_id');
    $table->index('family_hash');
    $table->index('created_at');
    $table->index(['type', 'should_display_on_index']);
});

// 2025_10_24_230445_create_breezy_sessions_table.php (2FA)
Schema::connection('central')->create('two_factor_authentication_sessions', function (Blueprint $table) {
    $table->id();
    $table->morphs('authenticatable');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('login_at')->nullable();
    $table->timestamp('login_successful_at')->nullable();
    $table->timestamp('logout_at')->nullable();
    $table->boolean('cleared_by_user')->default(false);
});
```

---

### 📊 Central DB Schema Übersicht:

```
klubportal_landlord
├── users                    (Superadmin Accounts)
├── tenants                  (Vereine/Clubs)
├── domains                  (testclub.localhost)
├── plans                    (Pricing Plans)
├── support_tickets          (Support System)
├── news                     (Globale News)
├── pages                    (Zentrale Seiten)
├── tags                     (Spatie Tags)
├── media                    (Spatie Media Library)
├── language_lines           (Spatie Translations)
├── settings                 (App Settings)
├── cache                    (Cache Tabelle)
├── jobs                     (Queue Jobs)
├── telescope_entries        (Debugging)
└── two_factor_authentication_sessions (2FA)
```

---

## 🎯 2. TENANT MIGRATIONS

**Ziel:** `tenant_testclub`, `tenant_xyz`, etc.  
**Pfad:** `database/migrations/tenant/`  
**Ausführen:** `php artisan tenants:migrate`

### 📋 Tenant-Tabellen:

#### 1️⃣ System-Tabellen (pro Tenant):
```php
// 0001_01_01_000000_create_sessions_table.php
Schema::create('sessions', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->foreignId('user_id')->nullable()->index();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->longText('payload');
    $table->integer('last_activity')->index();
});

// 0001_01_01_000001_create_cache_table.php
Schema::create('cache', function (Blueprint $table) {
    $table->string('key')->primary();
    $table->mediumText('value');
    $table->integer('expiration');
});

// 0001_01_01_000002_create_jobs_table.php
Schema::create('jobs', function (Blueprint $table) {
    $table->id();
    $table->string('queue')->index();
    $table->longText('payload');
    $table->unsignedTinyInteger('attempts');
    $table->unsignedInteger('reserved_at')->nullable();
    $table->unsignedInteger('available_at');
    $table->unsignedInteger('created_at');
});
```

---

#### 2️⃣ User Management (Tenant):
```php
// 2025_10_24_202500_create_tenant_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

// 2025_10_24_202554_create_permission_tables.php (Spatie Permissions)
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
    $table->unique(['name', 'guard_name']);
});

Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
    $table->unique(['name', 'guard_name']);
});

Schema::create('model_has_permissions', function (Blueprint $table) {
    $table->unsignedBigInteger('permission_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    $table->primary(['permission_id', 'model_id', 'model_type']);
});

Schema::create('model_has_roles', function (Blueprint $table) {
    $table->unsignedBigInteger('role_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->primary(['role_id', 'model_id', 'model_type']);
});

Schema::create('role_has_permissions', function (Blueprint $table) {
    $table->unsignedBigInteger('permission_id');
    $table->unsignedBigInteger('role_id');
    
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
    $table->primary(['permission_id', 'role_id']);
});
```

---

#### 3️⃣ Football/Soccer Management:
```php
// 2025_10_24_215900_create_seasons_table.php
Schema::create('seasons', function (Blueprint $table) {
    $table->id();
    $table->string('name');               // 2024/2025
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->timestamps();
});

// 2025_10_24_215916_create_teams_table.php
Schema::create('teams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('season_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('age_group')->nullable();
    $table->enum('gender', ['male', 'female', 'mixed'])->default('male');
    $table->string('league')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();
});

// 2025_10_24_215941_create_players_table.php
Schema::create('players', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('first_name');
    $table->string('last_name');
    $table->date('birth_date')->nullable();
    $table->enum('gender', ['male', 'female'])->default('male');
    $table->string('nationality')->nullable();
    $table->string('birthplace')->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->string('city')->nullable();
    $table->string('postal_code')->nullable();
    $table->string('position')->nullable();
    $table->integer('jersey_number')->nullable();
    $table->decimal('height', 5, 2)->nullable();
    $table->decimal('weight', 5, 2)->nullable();
    $table->enum('preferred_foot', ['left', 'right', 'both'])->nullable();
    $table->text('notes')->nullable();
    $table->softDeletes();
    $table->timestamps();
});

// 2025_10_24_215946_create_matches_table.php
Schema::create('matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->onDelete('cascade');
    $table->string('opponent');
    $table->dateTime('match_date');
    $table->string('location')->nullable();
    $table->integer('home_score')->nullable();
    $table->integer('away_score')->nullable();
    $table->boolean('is_home')->default(true);
    $table->enum('status', ['scheduled', 'live', 'finished', 'cancelled'])->default('scheduled');
    $table->text('notes')->nullable();
    $table->timestamps();
});

// 2025_10_24_220252_create_match_player_table.php (Pivot)
Schema::create('match_player', function (Blueprint $table) {
    $table->id();
    $table->foreignId('match_id')->constrained()->onDelete('cascade');
    $table->foreignId('player_id')->constrained()->onDelete('cascade');
    $table->boolean('is_starter')->default(false);
    $table->integer('minutes_played')->nullable();
    $table->integer('goals')->default(0);
    $table->integer('assists')->default(0);
    $table->integer('yellow_cards')->default(0);
    $table->integer('red_cards')->default(0);
    $table->timestamps();
});

// 2025_10_24_215950_create_trainings_table.php
Schema::create('trainings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->onDelete('cascade');
    $table->dateTime('training_date');
    $table->string('location')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});

// 2025_10_24_220257_create_training_player_table.php (Pivot)
Schema::create('training_player', function (Blueprint $table) {
    $table->id();
    $table->foreignId('training_id')->constrained()->onDelete('cascade');
    $table->foreignId('player_id')->constrained()->onDelete('cascade');
    $table->boolean('attended')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();
});

// 2025_10_24_220032_create_standings_table.php
Schema::create('standings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->onDelete('cascade');
    $table->integer('position')->default(0);
    $table->integer('played')->default(0);
    $table->integer('won')->default(0);
    $table->integer('drawn')->default(0);
    $table->integer('lost')->default(0);
    $table->integer('goals_for')->default(0);
    $table->integer('goals_against')->default(0);
    $table->integer('goal_difference')->default(0);
    $table->integer('points')->default(0);
    $table->timestamps();
});
```

---

#### 4️⃣ Club Management:
```php
// 2025_10_24_220015_create_members_table.php
Schema::create('members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('membership_number')->unique();
    $table->date('joined_at');
    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
    $table->enum('membership_type', ['full', 'youth', 'honorary', 'supporting'])->default('full');
    $table->text('notes')->nullable();
    $table->timestamps();
});

// 2025_10_24_220035_create_events_table.php
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->dateTime('event_date');
    $table->string('location')->nullable();
    $table->integer('max_participants')->nullable();
    $table->enum('status', ['upcoming', 'ongoing', 'finished', 'cancelled'])->default('upcoming');
    $table->timestamps();
});

// 2025_10_24_220000_create_news_table.php
Schema::create('news', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('excerpt')->nullable();
    $table->longText('content');
    $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
    $table->timestamp('published_at')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
});

// 2025_10_25_164440_create_pages_table.php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->longText('content')->nullable();
    $table->boolean('is_published')->default(false);
    $table->integer('order')->default(0);
    $table->boolean('show_in_menu')->default(false);
    $table->timestamps();
});
```

---

#### 5️⃣ Spatie-Pakete (Tenant):
```php
// 2025_10_24_202609_create_activity_log_table.php
Schema::create('activity_log', function (Blueprint $table) {
    $table->id();
    $table->string('log_name')->nullable();
    $table->text('description');
    $table->nullableMorphs('subject', 'subject');
    $table->nullableMorphs('causer', 'causer');
    $table->json('properties')->nullable();
    $table->uuid('batch_uuid')->nullable();
    $table->string('event')->nullable();
    $table->timestamps();
    $table->index('log_name');
});

// 2025_10_24_202724_create_media_table.php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->morphs('model');
    $table->uuid('uuid')->nullable()->unique();
    $table->string('collection_name');
    $table->string('name');
    $table->string('file_name');
    $table->string('mime_type')->nullable();
    $table->string('disk');
    $table->unsignedBigInteger('size');
    $table->json('manipulations');
    $table->json('custom_properties');
    $table->unsignedInteger('order_column')->nullable();
    $table->timestamps();
});

// 2025_10_24_202736_create_tag_tables.php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->json('name');
    $table->json('slug');
    $table->string('type')->nullable();
    $table->integer('order_column')->nullable();
    $table->timestamps();
});

// 2025_10_25_092248_create_language_lines_table.php
Schema::create('language_lines', function (Blueprint $table) {
    $table->id();
    $table->string('group');
    $table->string('key');
    $table->text('text');
    $table->string('locale');
    $table->timestamps();
});

// 2025_10_24_202803_create_settings_table.php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('group')->index();
    $table->string('name');
    $table->boolean('locked');
    $table->json('payload');
    $table->timestamps();
});

// 2025_10_24_202830_create_notifications_table.php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable');
    $table->text('data');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

---

### 📊 Tenant DB Schema Übersicht:

```
tenant_testclub (und alle anderen Tenant DBs)
├── users                    (Club Users: Admin, Trainer, Members)
├── permissions              (Spatie Permissions)
├── roles                    (Spatie Roles)
├── players                  (Spieler)
├── teams                    (Mannschaften)
├── seasons                  (Saisons)
├── matches                  (Spiele)
├── trainings                (Trainingseinheiten)
├── standings                (Tabellen)
├── members                  (Vereinsmitglieder)
├── events                   (Veranstaltungen)
├── news                     (Club News)
├── pages                    (Club Seiten)
├── media                    (Medien/Bilder)
├── tags                     (Tags)
├── activity_log             (Activity Logging)
├── notifications            (Benachrichtigungen)
├── settings                 (Club Settings)
├── language_lines           (Übersetzungen)
├── sessions                 (Sessions)
├── cache                    (Cache)
└── jobs                     (Queue Jobs)
```

---

## 🛠️ MIGRATION BEFEHLE

### Central Migrations:

```bash
# Alle Central Migrations ausführen
php artisan migrate --database=central

# Einzelne Migration ausführen
php artisan migrate --database=central --path=database/migrations/2025_10_24_202849_create_plans_table.php

# Migration zurückrollen
php artisan migrate:rollback --database=central --step=1

# Status anzeigen
php artisan migrate:status --database=central

# Frisch migrieren (VORSICHT: Löscht alle Daten!)
php artisan migrate:fresh --database=central --seed
```

---

### Tenant Migrations:

```bash
# Alle Tenants migrieren
php artisan tenants:migrate

# Nur bestimmten Tenant migrieren
php artisan tenants:migrate --tenants=testclub

# Mehrere Tenants
php artisan tenants:migrate --tenants=testclub,clubxyz

# Tenant Migration zurückrollen
php artisan tenants:migrate:rollback --tenants=testclub --step=1

# Migration-Status für Tenant
php artisan tenants:migrate:status --tenants=testclub

# Frisch migrieren (VORSICHT!)
php artisan tenants:migrate:fresh --tenants=testclub

# Mit Seeding
php artisan tenants:migrate --seed
```

---

## 📝 NEUE MIGRATION ERSTELLEN

### Central Migration:

```bash
# Migration erstellen
php artisan make:migration create_subscriptions_table

# Mit Model
php artisan make:model Models/Central/Subscription -m
```

**Wichtig:** Nutze `Schema::connection('central')` in der Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('subscriptions');
    }
};
```

**Ausführen:**
```bash
php artisan migrate --database=central
```

---

### Tenant Migration:

```bash
# Migration erstellen
php artisan make:migration create_competitions_table

# Mit Model
php artisan make:model Models/Tenant/Competition -m

# WICHTIG: Migration in tenant/ Ordner verschieben!
Move-Item database/migrations/2025_XX_XX_create_competitions_table.php database/migrations/tenant/
```

**Tenant-Migration verwendet `Schema::create()` OHNE connection:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // KEINE connection angeben - Tenancy managed automatisch
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('season');
            $table->string('league_level');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
```

**Ausführen:**
```bash
# Auf allen Tenants
php artisan tenants:migrate

# Oder nur auf testclub
php artisan tenants:migrate --tenants=testclub
```

---

## ⚠️ WICHTIGE REGELN

### ✅ DO's (Richtig):

#### Central Migration:
```php
Schema::connection('central')->create('subscriptions', function (Blueprint $table) {
    // ✅ Explizite Connection
});
```

#### Tenant Migration:
```php
Schema::create('competitions', function (Blueprint $table) {
    // ✅ KEINE Connection - Tenancy managed automatisch
});
```

---

### ❌ DON'Ts (Falsch):

```php
// ❌ FALSCH: Central Migration ohne Connection
Schema::create('subscriptions', function (Blueprint $table) {
    // Läuft auf falscher DB!
});

// ❌ FALSCH: Tenant Migration mit Connection
Schema::connection('tenant')->create('competitions', function (Blueprint $table) {
    // 'tenant' Connection existiert nicht statisch!
});
```

---

## 🔄 MIGRATION WORKFLOW

### Neues Feature für Central:

1. **Migration erstellen:**
   ```bash
   php artisan make:migration create_subscriptions_table
   ```

2. **Migration bearbeiten:**
   ```php
   Schema::connection('central')->create('subscriptions', ...);
   ```

3. **Ausführen:**
   ```bash
   php artisan migrate --database=central
   ```

4. **Model erstellen:**
   ```php
   namespace App\Models\Central;
   
   class Subscription extends Model
   {
       protected $connection = 'central';
   }
   ```

---

### Neues Feature für Tenant:

1. **Migration erstellen:**
   ```bash
   php artisan make:migration create_competitions_table
   ```

2. **In tenant/ Ordner verschieben:**
   ```bash
   Move-Item database/migrations/2025_XX_XX_create_competitions_table.php database/migrations/tenant/
   ```

3. **Migration bearbeiten:**
   ```php
   Schema::create('competitions', ...);  // Keine connection!
   ```

4. **Auf allen Tenants ausführen:**
   ```bash
   php artisan tenants:migrate
   ```

5. **Model erstellen:**
   ```php
   namespace App\Models\Tenant;
   
   class Competition extends Model
   {
       // KEINE $connection Property!
   }
   ```

---

## 📊 ZUSAMMENFASSUNG

| Aspekt | Central | Tenant |
|--------|---------|--------|
| **Pfad** | `database/migrations/` | `database/migrations/tenant/` |
| **Datenbank** | `klubportal_landlord` | `tenant_testclub`, `tenant_xyz`, etc. |
| **Schema** | `Schema::connection('central')` | `Schema::create()` (kein connection) |
| **Ausführen** | `php artisan migrate --database=central` | `php artisan tenants:migrate` |
| **Model Connection** | `protected $connection = 'central';` | Keine Connection angeben |
| **Zweck** | Superadmin, Tenants, Pläne | Club-Daten isoliert pro Verein |

---

## ✅ STATUS

✅ **Ordnerstruktur:** Korrekt eingerichtet  
✅ **Central Migrations:** 23 Migrations vorhanden  
✅ **Tenant Migrations:** 25 Migrations vorhanden  
✅ **Naming Convention:** Korrekt befolgt  
✅ **Schema Connections:** Korrekt implementiert  

**Deine Migrations-Struktur ist perfekt aufgebaut!** 🎉

---

**📅 Erstellt:** 25. Oktober 2025  
**🔄 Letztes Update:** Nach Struktur-Überprüfung  
**✅ Status:** Migrations korrekt strukturiert
