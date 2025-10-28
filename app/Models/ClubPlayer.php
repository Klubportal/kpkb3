<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubPlayer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'popular_name',
        'date_of_birth',
        'nationality',
        'photo_url',
        'position',
        'jersey_number',
        'email',
        'phone',
        'mobile',
        'address',
        'postal_code',
        'city',
        'parent1_name',
        'parent1_email',
        'parent1_phone',
        'parent1_mobile',
        'parent2_name',
        'parent2_email',
        'parent2_phone',
        'parent2_mobile',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'has_medical_clearance',
        'medical_clearance_date',
        'medical_notes',
        'allergies',
        'notes',
        'active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'medical_clearance_date' => 'date',
        'has_medical_clearance' => 'boolean',
        'active' => 'boolean',
    ];
}
