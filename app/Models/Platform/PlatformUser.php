<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlatformUser extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'last_login_at'];
    protected $hidden = ['password'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function clubs()
    {
        return $this->hasMany(Club::class, 'admin_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Attributes
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getAvatarAttribute()
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=dc2626&color=fff";
    }
}
