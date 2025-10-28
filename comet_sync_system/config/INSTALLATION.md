# Config Snippet für config/services.php

Füge dies in deine config/services.php ein:

```php
    /*
    |--------------------------------------------------------------------------
    | Comet API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfiguration für die Comet FIFA API Integration
    | NK Prigorje: Team FIFA ID 598
    |
    */

    'comet' => [
        'api_url' => env('COMET_API_URL', 'https://api-hns.analyticom.de/api/export/comet'),
        'username' => env('COMET_USERNAME'),
        'password' => env('COMET_PASSWORD'),
    ],
```

# .env Konfiguration

Füge dies in deine .env Datei ein:

```env
# Comet API
COMET_API_URL=https://api-hns.analyticom.de/api/export/comet
COMET_USERNAME=nkprigorje
COMET_PASSWORD=3c6nR$dS
```
