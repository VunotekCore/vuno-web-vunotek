<?php
/**
 * Archivo de Configuración - EJEMPLO
 *
 * Copia este archivo como config.php y rellena las credenciales reales.
 * config.php está en .gitignore y NO se sube a Git.
 */

return [
    'database' => [
        'host'     => 'localhost',
        'name'     => 'vunotek_db',
        'username' => 'vunotek_user',
        'password' => 'TU_PASSWORD_AQUI',
        'charset'  => 'utf8mb4',
    ],

    'jwt' => [
        'secret'     => 'GENERAR_CON_openssl_rand_hex_32',
        'expires_in' => 86400,
        'issuer'     => 'vunotek.com',
    ],

    'email' => [
        'smtp_host'       => 'smtp.hostinger.com',
        'smtp_port'       => 465,
        'smtp_encryption' => 'ssl',
        'smtp_username'   => 'projects@vunotek.com',
        'smtp_password'   => 'TU_PASSWORD_SMTP',
        'from_email'      => 'projects@vunotek.com',
        'from_name'       => 'Vunotek - Contact Web Form',
        'to_email'        => [
            'projects@vunotek.com' => 'Equipo Vunotek',
        ],
        'reply_to_email'  => 'comercial@vunotek.com',
    ],

    'google_calendar' => [
        'client_id'              => 'TU_CLIENT_ID',
        'client_secret'          => 'TU_CLIENT_SECRET',
        'refresh_token'          => 'TU_REFRESH_TOKEN',
        'calendar_id'            => 'TU_CALENDAR_ID',
        'additional_calendars'   => [],
        'additional_attendees'   => [
            'projects@vunotek.com' => 'Solicitud de Reunión',
        ],
        'timezone' => 'America/Managua',
    ],

    'app' => [
        'site_url' => 'https://vunotek.com',
        'allowed_origins' => [
            'https://vunotek.com',
            'https://www.vunotek.com',
            'http://localhost:4321',
        ],
        'debug' => false,
    ],
];
