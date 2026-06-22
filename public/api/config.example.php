<?php
/**
 * Archivo de Configuración - Ejemplo
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'config.php' en el mismo directorio
 * 2. Completa todas las credenciales con tus datos reales
 * 3. NO subas config.php a Git (ya está en .gitignore)
 */

return [
    // ============================================
    // CONFIGURACIÓN DE EMAIL (HOSTINGER SMTP)
    // ============================================
    'email' => [
        'smtp_host' => 'smtp.hostinger.com',
        'smtp_port' => 465,
        'smtp_encryption' => 'ssl',
        'smtp_username' => 'tu-email@tudominio.com',
        'smtp_password' => 'tu-contraseña-aqui',
        'from_email' => 'contacto@tudominio.com',
        'from_name' => 'Vunotek',
        'to_email' => [
            'projects@vunotek.com' => 'Equipo Vunotek',
        ],
        'reply_to_email' => 'contacto@tudominio.com',
    ],

    // ============================================
    // CONFIGURACIÓN DE GOOGLE CALENDAR API
    // ============================================
    'google_calendar' => [
        'client_id' => 'tu-client-id.apps.googleusercontent.com',
        'client_secret' => 'tu-client-secret',
        'refresh_token' => 'tu-refresh-token',
        'calendar_id' => 'primary',
        'additional_calendars' => [],
        'additional_attendees' => [
            'projects@vunotek.com' => 'Equipo Vunotek',
        ],
        'timezone' => 'America/Managua',
    ],

    // ============================================
    // CONFIGURACIÓN GENERAL
    // ============================================
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
