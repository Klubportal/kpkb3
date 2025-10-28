# ⚠️ ARCHIVED FILES - DO NOT USE

**Date Archived**: October 28, 2025

## ⛔ These files are DEPRECATED and NO LONGER IN USE

All sync functionality has been moved to proper Laravel architecture:

### ✅ Use Instead:

**Console Commands** (in `app/Console/Commands/`):
- `php artisan comet:sync-all` - Sync everything
- `php artisan comet:sync-matches` - Sync matches only
- `php artisan comet:sync-rankings` - Sync rankings only
- `php artisan comet:sync-topscorers` - Sync top scorers only
- `php artisan tenant:sync-comet --all` - Sync all tenants

**Services** (in `app/Services/`):
- `CometApiService` - API communication
- `CometSyncService` - Sync business logic

**Admin Interface**:
- `/admin/sync` - Manual sync dashboard
- API endpoints for triggering syncs

### 📋 Archived Files Count:

- **45 sync scripts** (`sync_*.php`)
- **94 check scripts** (`check_*.php`) 
- **53 test scripts** (`test_*.php`)
- **21 analyze scripts** (`analyze_*.php`)

**Total: 213 files**

### ❌ Why Were These Archived?

1. **Code Duplication** - Same logic in multiple places
2. **Inconsistent DB Access** - Mixed mysqli, DB facade, Eloquent
3. **No Logging** - Output lost after execution
4. **Not Laravel Standard** - Should use Artisan commands
5. **Maintenance Nightmare** - Hard to update, test, debug

### 🗑️ Safe to Delete?

**After 30 days** (November 27, 2025), if the new system works perfectly, these files can be permanently deleted.

### 🔍 If You Need Historical Reference:

Check Git history or this archive directory. All functionality has been preserved and improved in the new architecture.

---

**DO NOT RUN THESE SCRIPTS DIRECTLY**

Use the Laravel Artisan commands instead.
