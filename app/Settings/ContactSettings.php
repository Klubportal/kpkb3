<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public ?string $company_name;
    public ?string $street;
    public ?string $postal_code;
    public ?string $city;
    public ?string $country;
    public ?string $phone;
    public ?string $fax;
    public ?string $mobile;
    public ?string $email;
    public ?string $google_maps_url;
    public ?string $google_maps_embed;

    public static function group(): string
    {
        return 'contact';
    }
}
