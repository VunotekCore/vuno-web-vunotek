<?php
/**
 * Script para generar Refresh Token de Google Calendar (versión ligera)
 * Ejecuta este script y sigue las instrucciones
 */

require_once __DIR__ . '/../vendor/LightweightGoogleCalendar.php';

$configFile = __DIR__ . '/api/config.php';
if (!file_exists($configFile)) {
    echo "<p style='color:red;font-weight:bold;'>❌ Error: No se encuentra el archivo de configuración.</p>";
    echo "<p>Copia <code>public/api/config.example.php</code> como <code>public/api/config.php</code> y completa las credenciales.</p>";
    exit;
}

$config = require $configFile;

$clientId = $config['google_calendar']['client_id'];
$clientSecret = $config['google_calendar']['client_secret'];
$redirectUri = ($config['app']['site_url'] ?? 'https://vunotek.com') . '/get-refresh-token.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Generar Refresh Token - Vunotek</title>\n<style>
body{font-family:'Segoe UI',Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#0b1326;color:#e2e8f0;}
h1{color:#42b883;}h2{color:#dae2fd;}h3{color:#dae2fd;}
pre{background:#1a2332;padding:15px;border-radius:8px;overflow-x:auto;color:#e2e8f0;border:1px solid #334155;}
code{background:#1a2332;padding:2px 6px;border-radius:4px;color:#42b883;}
.success{color:#42b883;font-weight:bold;}
.error{color:#ef4444;}.warning{color:#f59e0b;}
a{color:#42b883;}a:hover{color:#69dca4;}
.btn{display:inline-block;background:#42b883;color:#0b1326;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;}
.btn:hover{background:#69dca4;box-shadow:0 0 20px rgba(66,184,131,0.4);}
ul,ol{line-height:1.8;}.box{background:#1a2332;border:1px solid #334155;border-radius:8px;padding:20px;margin:20px 0;}
</style>\n</head>\n<body>\n";

echo "<h1>🔑 Generador de Refresh Token</h1>\n";
echo "<p style='color:#94a3b8;'>Para la integración de Google Calendar en Vunotek</p>\n";

$scope = 'https://www.googleapis.com/auth/calendar';
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope,
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

if (!isset($_GET['code'])) {
    echo "<h2>Paso 1: Autorizar la Aplicación</h2>\n";
    echo "<p>Haz clic en el botón para autorizar el acceso a Google Calendar:</p>\n";
    echo "<p><a href='$authUrl' class='btn'>🔐 Autorizar con Google</a></p>\n";

    echo "<div class='box'>\n";
    echo "<h3>⚠️ Requisitos</h3>\n";
    echo "<ul>\n";
    echo "<li>El archivo <code>public/api/config.php</code> debe tener el <strong>Client ID</strong> y <strong>Client Secret</strong> correctos</li>\n";
    echo "<li>En <strong>Google Cloud Console</strong>, agrega esta URL como <code>Authorized redirect URI</code>:</li>\n";
    echo "</ul>\n";
    echo "<pre>$redirectUri</pre>\n";
    echo "<p>Inicia sesión con la cuenta de Google del calendario (recommendado: <strong>projects@vunotek.com</strong>)</p>\n";
    echo "</div>\n";

    echo "<div class='box'>\n";
    echo "<h3>📋 Configuración Actual</h3>\n";
    echo "<pre>";
    echo "Client ID: $clientId\n";
    echo "Redirect URI: $redirectUri\n";
    echo "Scope: Google Calendar API (events)\n";
    echo "</pre>\n";
    echo "</div>\n";

} else {
    echo "<h2>Paso 2: Generando Tokens...</h2>\n";

    try {
        $data = [
            'code' => $_GET['code'],
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $token = json_decode($response, true);

        if (isset($token['error'])) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($token['error'] ?? 'Desconocido') . "</p>\n";
            echo "<div class='box'>\n";
            echo "<h3>Soluciones:</h3>\n";
            echo "<ul>\n";
            echo "<li>Verifica que el <strong>Client ID</strong> y <strong>Client Secret</strong> sean correctos en <code>config.php</code></li>\n";
            echo "<li>Asegúrate de que la <strong>Redirect URI</strong> esté configurada en Google Cloud Console</li>\n";
            echo "<li><a href='?'>Intentar de nuevo</a></li>\n";
            echo "</ul>\n";
            echo "<pre>" . htmlspecialchars(print_r($token, true)) . "</pre>\n";
            echo "</div>\n";
        } else {
            echo "<p class='success'>✅ Tokens generados correctamente</p>\n";

            if (isset($token['refresh_token'])) {
                echo "<div class='box' style='border-color:#42b883;'>\n";
                echo "<h3>🎉 Refresh Token</h3>\n";
                echo "<p>Copia este token y pégalo en <code>public/api/config.php</code>:</p>\n";
                echo "<pre style='background:#064e3b;border-color:#42b883;font-size:14px;'>" . htmlspecialchars($token['refresh_token']) . "</pre>\n";

                echo "<h3>📝 Instrucciones:</h3>\n";
                echo "<ol>\n";
                echo "<li><strong>Copia</strong> el refresh token de arriba</li>\n";
                echo "<li><strong>Abre</strong> <code>public/api/config.php</code></li>\n";
                echo "<li><strong>Pega</strong> el token en el campo <code>'refresh_token'</code></li>\n";
                echo "</ol>\n";

                echo "<pre>";
                echo "'google_calendar' => [\n";
                echo "    'client_id' => '$clientId',\n";
                echo "    'client_secret' => '...',\n";
                echo "    'refresh_token' => '" . htmlspecialchars($token['refresh_token']) . "',\n";
                echo "    'calendar_id' => 'primary',\n";
                echo "    'timezone' => 'America/Managua',\n";
                echo "],\n";
                echo "</pre>\n";

                echo "<p>✅ ¡Listo! Después de guardar, puedes probar agendando una reunión desde el formulario de contacto.</p>\n";

            } else {
                echo "<p class='warning'>⚠️ No se generó un refresh token nuevo.</p>\n";
                echo "<p>Esto ocurre si ya autorizaste antes. Para forzar uno nuevo:</p>\n";
                echo "<ol>\n";
                echo "<li>Ve a <a href='https://myaccount.google.com/permissions' target='_blank'>Google Account Permissions</a></li>\n";
                echo "<li>Revoca el acceso a la aplicación</li>\n";
                echo "<li><a href='?'>Intentar de nuevo</a></li>\n";
                echo "</ol>\n";
            }
        }

    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p><a href='?'>Intentar de nuevo</a></p>\n";
    }
}

echo "</body>\n</html>";
