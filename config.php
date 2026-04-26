<?php
return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'ZipFood',
        'brand' => getenv('APP_BRAND') ?: 'ZipFood',
        'environment' => getenv('APP_ENV') ?: 'local',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOL),
        'timezone' => getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'zipfood',
        'user' => getenv('DB_USER') ?: 'adv',
        'pass' => getenv('DB_PASS') ?: 'gigalele',
    ],
    'auth' => [
        'admin_user' => getenv('ADMIN_USER') ?: 'admin',
        'admin_password' => getenv('ADMIN_PASSWORD') ?: 'Admin@123',
        'admin_password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '',
    ],
    'integrations' => [
        'ga4_id' => getenv('GA4_ID') ?: 'G-Y5GYZ94XHE',
        'google_ads_id' => getenv('GOOGLE_ADS_ID') ?: 'AW-XXXXXXXXX',
        'gtag_conversion_label' => getenv('GTAG_CONVERSION_LABEL') ?: 'ABC123xyz',
        'adsense_client_id' => getenv('ADSENSE_CLIENT_ID') ?: 'ca-pub-3573552933822285',
    ],
];
