<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'platform_tenants';

    protected $fillable = [
        'club_id',
        'database_name',
        'subdomain',
        'is_initialized',
        'initialized_at',
        'last_backup_at',
        'storage_used_mb',
        'storage_limit_mb',
    ];

    protected $casts = [
        'is_initialized' => 'boolean',
        'initialized_at' => 'datetime',
        'last_backup_at' => 'datetime',
        'storage_used_mb' => 'float',
        'storage_limit_mb' => 'float',
    ];

    /**
     * Relationships
     */
    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    /**
     * Methods
     */
    public function getDatabaseConnection()
    {
        return "tenant_{$this->club_id}";
    }

    public function getStoragePercentageAttribute()
    {
        if ($this->storage_limit_mb == 0) {
            return 0;
        }
        return round(($this->storage_used_mb / $this->storage_limit_mb) * 100, 2);
    }

    public function isStorageAlmostFull()
    {
        return $this->storage_percentage >= 90;
    }

    public function getStorageRemainingAttribute()
    {
        return $this->storage_limit_mb - $this->storage_used_mb;
    }
}
