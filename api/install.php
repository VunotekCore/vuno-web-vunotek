<?php
declare(strict_types=1);
/**
 * Vunotek — Web Installer
 *
 * One-time setup wizard for production deployment on Hostinger.
 * Flow:
 *   1. DB connection → 2. Run schema.sql → 3. Admin + JWT setup → Done
 *
 * Security:
 *   - Creates .installer-lock after success (blocks re-run)
 *   - Validates DB connection before proceeding
 *   - JWT secret generated with bin2hex(random_bytes(32))
 *   - All passwords hashed with PASSWORD_BCRYPT
 *   - Deletes install.php prompt after completion
 */

define('INSTALLER_VERSION', '1.0.0');
define('LOCK_FILE', __DIR__ . '/.installer-lock');

// --- Lock check ---
$isInstalled = false;
if (file_exists(LOCK_FILE)) {
    $isInstalled = true;
} elseif (file_exists(__DIR__ . '/config.php')) {
    $config = @require __DIR__ . '/config.php';
    if (is_array($config) && !empty($config['database']['host'])) {
        try {
            $db = $config['database'];
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset'] ?? 'utf8mb4');
            new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $isInstalled = true;
        } catch (\Exception $e) {
            // Connection failed — allow reinstall
        }
    }
}

if ($isInstalled) {
    renderAlreadyInstalled();
    exit;
}

// --- Step Router ---
$step = isset($_POST['_step']) ? (int)$_POST['_step'] : 1;

// =========================================================================
// STEP 1: Database Connection
// =========================================================================
if ($step === 1) {
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_host'])) {
        $dbHost = trim($_POST['db_host']);
        $dbPort = trim($_POST['db_port']) ?: '3306';
        $dbName = trim($_POST['db_name']);
        $dbUser = trim($_POST['db_user']);
        $dbPass = $_POST['db_pass'] ?? '';

        if ($dbHost === '' || $dbName === '' || $dbUser === '') {
            $error = 'Completá los campos obligatorios (Host, Base de Datos, Usuario).';
        } else {
            try {
                $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort);
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);

                // Create database if it doesn't exist
                $safeDb = str_replace('`', '``', $dbName);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$safeDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$safeDb`");

                // Verify connection
                $checkDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
                new PDO($checkDsn, $dbUser, $dbPass, [PDO::ATTR_TIMEOUT => 3]);

                renderStep('Conexión Exitosa', 'Base de Datos', 1, 3);
                ?>
                <div class="success">✓ Conexión establecida correctamente a <strong><?= htmlspecialchars($dbName) ?></strong></div>
                <p class="desc">La base de datos está accesible. Ahora vamos a crear las tablas.</p>
                <form method="post">
                    <input type="hidden" name="_step" value="2">
                    <input type="hidden" name="db_host" value="<?= htmlspecialchars($dbHost) ?>">
                    <input type="hidden" name="db_port" value="<?= htmlspecialchars($dbPort) ?>">
                    <input type="hidden" name="db_name" value="<?= htmlspecialchars($dbName) ?>">
                    <input type="hidden" name="db_user" value="<?= htmlspecialchars($dbUser) ?>">
                    <input type="hidden" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>">
                    <button type="submit" class="btn">Instalar Base de Datos →</button>
                </form>
                <?php
                renderFooter();
                exit;

            } catch (\PDOException $e) {
                $error = 'Error de conexión: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    renderStep('Configurar Base de Datos', 'Base de Datos', 1, 3);
    if ($error !== '') echo '<div class="error">' . $error . '</div>';
    ?>
    <p class="desc">Ingresá los datos de conexión MySQL de tu hosting (Hostinger → Bases de Datos → MySQL).</p>
    <form method="post">
        <input type="hidden" name="_step" value="1">
        <div class="grid-2">
            <div>
                <label for="db_host">Host *</label>
                <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                <div class="hint">Ej: localhost o mysql.hostinger.com</div>
            </div>
            <div>
                <label for="db_port">Puerto</label>
                <input type="text" id="db_port" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>">
            </div>
        </div>
        <label for="db_name">Nombre de la Base de Datos *</label>
        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'vuno_web') ?>" required>
        <div class="hint">Se creará automáticamente si no existe.</div>
        <label for="db_user">Usuario *</label>
        <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required autocomplete="off">
        <label for="db_pass">Contraseña</label>
        <input type="password" id="db_pass" name="db_pass" value="" autocomplete="off">
        <button type="submit" class="btn">Probar Conexión →</button>
    </form>
    <?php
    renderFooter();
    exit;
}

// =========================================================================
// STEP 2: Execute schema.sql
// =========================================================================
if ($step === 2) {
    $dbHost = $_POST['db_host'] ?? '';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    if ($dbHost === '' || $dbName === '' || $dbUser === '') {
        renderStep('Error', 'Instalación', 2, 3);
        echo '<div class="error">Faltan datos de conexión. Volvé al paso 1.</div>';
        echo '<form method="post"><input type="hidden" name="_step" value="1"><button class="btn">Volver</button></form>';
        renderFooter();
        exit;
    }

    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        renderStep('Error', 'Instalación', 2, 3);
        echo '<div class="error">No se encontró <code>api/schema.sql</code>.</div>';
        renderFooter();
        exit;
    }

    try {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
        ]);

        $sql = file_get_contents($schemaFile);
        if ($sql === false) throw new \RuntimeException('No se pudo leer schema.sql');

        $stmts = splitSql($sql);
        $count = 0;
        $errors = [];
        foreach ($stmts as $stmt) {
            try {
                $pdo->exec($stmt);
                $count++;
            } catch (\PDOException $e) {
                $errors[] = htmlspecialchars($e->getMessage());
            }
        }

        renderStep('Base de Datos Instalada', 'Instalación', 2, 3);
        if (empty($errors)) {
            echo '<div class="success">✓ Schema instalado correctamente. <strong>' . $count . '</strong> sentencias ejecutadas.</div>';
        } else {
            echo '<div class="success">✓ Instalación completada con ' . count($errors) . ' advertencias. ' . $count . ' sentencias ejecutadas.</div>';
            echo '<ul>';
            foreach (array_slice($errors, 0, 5) as $e) {
                echo '<li>' . $e . '</li>';
            }
            if (count($errors) > 5) echo '<li>... y ' . (count($errors) - 5) . ' más</li>';
            echo '</ul>';
        }
        ?>
        <p class="desc">Ahora configurá el administrador y la clave JWT.</p>
        <form method="post">
            <input type="hidden" name="_step" value="3">
            <input type="hidden" name="db_host" value="<?= htmlspecialchars($dbHost) ?>">
            <input type="hidden" name="db_port" value="<?= htmlspecialchars($dbPort) ?>">
            <input type="hidden" name="db_name" value="<?= htmlspecialchars($dbName) ?>">
            <input type="hidden" name="db_user" value="<?= htmlspecialchars($dbUser) ?>">
            <input type="hidden" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>">
            <button type="submit" class="btn">Configurar Admin →</button>
        </form>
        <?php
        renderFooter();
        exit;

    } catch (\Exception $e) {
        renderStep('Error', 'Instalación', 2, 3);
        echo '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<form method="post"><input type="hidden" name="_step" value="1"><button class="btn">Volver</button></form>';
        renderFooter();
        exit;
    }
}

// =========================================================================
// STEP 3: Admin User + JWT + Config
// =========================================================================
if ($step === 3) {
    $dbHost = $_POST['db_host'] ?? '';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_email'])) {
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';
        $imagekitKey = trim($_POST['imagekit_key'] ?? '');
        $imagekitEndpoint = trim($_POST['imagekit_endpoint'] ?? '');
        $smtpPassword = $_POST['smtp_password'] ?? '';

        if ($adminEmail === '' || $adminName === '' || $adminPass === '') {
            $error = 'Completá todos los campos obligatorios.';
        } elseif (strlen($adminPass) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($adminPass !== $adminPassConfirm) {
            $error = 'Las contraseñas no coinciden.';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresá un email válido.';
        } else {
            try {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                // Find admin role
                $roleId = 1;
                try {
                    $row = $pdo->query("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
                    if ($row) $roleId = (int)$row['id'];
                } catch (\Exception $e) {
                    // Table may not exist yet
                }

                // Create or update admin user
                $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 10]);
                $stmt = $pdo->prepare('UPDATE users SET email = ?, password = ?, name = ? WHERE role_id = ? LIMIT 1');
                $stmt->execute([$adminEmail, $hash, $adminName, $roleId]);
                if ($stmt->rowCount() === 0) {
                    $stmt = $pdo->prepare('INSERT INTO users (email, password, name, role_id) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$adminEmail, $hash, $adminName, $roleId]);
                }

                // Generate JWT secret
                $jwtSecret = bin2hex(random_bytes(32));

                // Determine allowed origins
                $allowedOrigins = "'https://vunotek.com',\n            'https://www.vunotek.com'";

                // Write config.php
                $now = date('Y-m-d H:i:s');
                $configContent = "<?php
/**
 * Vunotek API Configuration
 * Generated by install.php on {$now}
 *
 * WARNING: Do not expose this file publicly.
 */

return [
    'database' => [
        'host'     => " . var_export($dbHost, true) . ",
        'name'     => " . var_export($dbName, true) . ",
        'username' => " . var_export($dbUser, true) . ",
        'password' => " . var_export($dbPass, true) . ",
        'charset'  => 'utf8mb4',
    ],

    'jwt' => [
        'secret'     => '{$jwtSecret}',
        'expires_in' => 86400,
        'issuer'     => 'vunotek.com',
    ],

    'imagekit' => [
        'private_key'  => " . var_export($imagekitKey, true) . ",
        'url_endpoint' => " . var_export($imagekitEndpoint ?: 'https://ik.imagekit.io/vijys5g3r', true) . ",
    ],

    'email' => [
        'smtp_host'       => 'smtp.hostinger.com',
        'smtp_port'       => 465,
        'smtp_encryption' => 'ssl',
        'smtp_username'   => 'projects@vunotek.com',
        'smtp_password'   => " . var_export($smtpPassword, true) . ",
        'from_email'      => 'projects@vunotek.com',
        'from_name'       => 'Vunotek - Contact Web Form',
        'to_email'        => [
            'projects@vunotek.com' => 'Equipo Vunotek',
        ],
        'reply_to_email'  => 'comercial@vunotek.com',
    ],

    'google_calendar' => [
        'client_id'              => '',
        'client_secret'          => '',
        'refresh_token'          => '',
        'calendar_id'            => '',
        'additional_calendars'   => [],
        'additional_attendees'   => [
            'projects@vunotek.com' => 'Solicitud de Reunión',
            'comercial@vunotek.com' => 'Solicitud de Reunión',
        ],
        'timezone' => 'America/Managua',
    ],

    'app' => [
        'site_url' => 'https://vunotek.com',
        'allowed_origins' => [
            {$allowedOrigins},
        ],
        'debug' => false,
    ],
];
";

                $configPath = __DIR__ . '/config.php';
                if (file_put_contents($configPath, $configContent) === false) {
                    throw new \RuntimeException('No se pudo escribir config.php. Verificá permisos de escritura.');
                }

                // Create lock file
                file_put_contents(LOCK_FILE, date('c') . " — Installed via install.php\n");

                // Success!
                renderStep('¡Instalación Completa!', 'Finalizado', 3, 3);
                ?>
                <div class="success">✓ Vunotek API está configurada.</div>
                <ul>
                    <li><strong>Admin:</strong> <?= htmlspecialchars($adminEmail) ?></li>
                    <li><strong>JWT Secret:</strong> <code><?= htmlspecialchars(substr($jwtSecret, 0, 16)) ?>...</code> (generado con 32 bytes de entropía)</li>
                    <li><strong>DB:</strong> <?= htmlspecialchars($dbName) ?> @ <?= htmlspecialchars($dbHost) ?></li>
                </ul>
                <div class="warning-box">
                    <strong>⚠️ Importante:</strong>
                    <ul>
                        <li>Eliminá <code>install.php</code> del servidor por seguridad</li>
                        <li>El archivo <code>config.php</code> ya contiene las credenciales</li>
                    </ul>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:20px;">
                    <a href="/admin/login" class="btn" style="display:block;text-align:center;text-decoration:none;">Ir al Panel Admin →</a>
                    <a href="/" class="btn secondary" style="display:block;text-align:center;text-decoration:none;">Ver Sitio Web →</a>
                </div>
                <?php
                renderFooter();
                exit;

            } catch (\Exception $e) {
                $error = 'Error al guardar: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    renderStep('Configurar Administrador', 'Admin y JWT', 3, 3);
    if ($error !== '') echo '<div class="error">' . $error . '</div>';
    ?>
    <p class="desc">Creá el usuario administrador y configurá las credenciales. El JWT secret se genera automáticamente con alta entropía.</p>
    <form method="post">
        <input type="hidden" name="_step" value="3">
        <input type="hidden" name="db_host" value="<?= htmlspecialchars($dbHost) ?>">
        <input type="hidden" name="db_port" value="<?= htmlspecialchars($dbPort) ?>">
        <input type="hidden" name="db_name" value="<?= htmlspecialchars($dbName) ?>">
        <input type="hidden" name="db_user" value="<?= htmlspecialchars($dbUser) ?>">
        <input type="hidden" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>">

        <h2 class="section-title">Administrador</h2>
        <label for="admin_email">Email *</label>
        <input type="email" id="admin_email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@vunotek.com') ?>" required>
        <label for="admin_name">Nombre *</label>
        <input type="text" id="admin_name" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Daniel Flores') ?>" required>
        <label for="admin_pass">Contraseña *</label>
        <input type="password" id="admin_pass" name="admin_pass" required minlength="8" autocomplete="new-password">
        <div class="hint">Mínimo 8 caracteres.</div>
        <label for="admin_pass_confirm">Confirmar Contraseña *</label>
        <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required minlength="8" autocomplete="new-password">

        <h2 class="section-title">ImageKit (opcional)</h2>
        <label for="imagekit_key">Private API Key</label>
        <input type="text" id="imagekit_key" name="imagekit_key" value="<?= htmlspecialchars($_POST['imagekit_key'] ?? '') ?>" autocomplete="off">
        <div class="hint">Si lo dejás vacío, se usa el endpoint por defecto.</div>
        <label for="imagekit_endpoint">URL Endpoint</label>
        <input type="text" id="imagekit_endpoint" name="imagekit_endpoint" value="<?= htmlspecialchars($_POST['imagekit_endpoint'] ?? 'https://ik.imagekit.io/vijys5g3r') ?>">

        <h2 class="section-title">SMTP (opcional)</h2>
        <label for="smtp_password">Password SMTP (projects@vunotek.com)</label>
        <input type="password" id="smtp_password" name="smtp_password" value="" autocomplete="off">
        <div class="hint">Necesario para el formulario de contacto. Si lo dejás vacío, configurás después.</div>

        <button type="submit" class="btn">Finalizar Instalación</button>
    </form>
    <?php
    renderFooter();
    exit;
}

// Fallback
header('Location: ?');
exit;

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================

function splitSql(string $sql): array
{
    $sql = preg_replace('/CREATE\s+DATABASE\s+\S+\s*;/i', '', $sql);
    $sql = preg_replace('/USE\s+\S+\s*;/i', '', $sql);

    $stmts = [];
    $current = '';
    $inString = false;
    $len = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $c = $sql[$i];
        if ($inString) {
            $current .= $c;
            if ($c === '\'' && ($i === 0 || $sql[$i - 1] !== '\\')) $inString = false;
            continue;
        }
        if ($c === '\'') { $inString = true; $current .= $c; continue; }
        if ($c === ';') {
            $trimmed = trim($current);
            if ($trimmed !== '' && !str_starts_with($trimmed, '--') && !str_starts_with($trimmed, '#')) {
                $stmts[] = $trimmed;
            }
            $current = '';
            continue;
        }
        $isBlank = trim($current) === '';
        if ($isBlank && $c === '-' && isset($sql[$i + 1]) && $sql[$i + 1] === '-') {
            $eol = strpos($sql, "\n", $i);
            $i = ($eol !== false) ? $eol : $len - 1;
            continue;
        }
        if ($isBlank && $c === '#') {
            $eol = strpos($sql, "\n", $i);
            $i = ($eol !== false) ? $eol : $len - 1;
            continue;
        }
        $current .= $c;
    }

    $trimmed = trim($current);
    if ($trimmed !== '' && !str_starts_with($trimmed, '--') && !str_starts_with($trimmed, '#')) {
        $stmts[] = $trimmed;
    }

    return $stmts;
}

function renderStep(string $title, string $stepTitle, int $step, int $totalSteps): void
{
    $pct = (int)(($step / $totalSteps) * 100);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> — Vunotek Installer</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #0b1326; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
            .card { background: #111b30; border: 1px solid #1e2d4a; box-shadow: 0 8px 32px rgba(0,0,0,.3); padding: 48px 40px; max-width: 520px; width: 100%; margin: 24px; }
            .step-bar { height: 3px; background: #1e2d4a; border-radius: 2px; margin-bottom: 24px; overflow: hidden; }
            .step-fill { height: 100%; background: #42b883; transition: width .3s; }
            .step-indicator { font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: #42b883; margin-bottom: 8px; }
            h1 { font-size: 22px; font-weight: 600; letter-spacing: -.01em; margin-bottom: 4px; color: #fff; }
            .desc { font-size: 14px; line-height: 1.6; color: #8899b4; margin-bottom: 20px; }
            label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b7fa3; margin-bottom: 6px; margin-top: 18px; }
            label:first-of-type { margin-top: 0; }
            input { width: 100%; padding: 10px 0; border: none; border-bottom: 1px solid #1e2d4a; background: transparent; font-size: 15px; color: #e2e8f0; outline: none; transition: border-color .15s; }
            input:focus { border-bottom-color: #42b883; }
            .hint { font-size: 12px; color: #5a6d8a; margin-top: 4px; margin-bottom: 4px; }
            .error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; padding: 12px 16px; font-size: 13px; border-radius: 6px; margin-bottom: 16px; }
            .success { background: rgba(66,184,131,.1); border: 1px solid rgba(66,184,131,.3); color: #69dca4; padding: 12px 16px; font-size: 13px; border-radius: 6px; margin-bottom: 16px; }
            .warning-box { background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.25); color: #fbbf24; padding: 16px; font-size: 13px; border-radius: 6px; margin-top: 16px; line-height: 1.6; }
            .warning-box ul { margin: 8px 0 0 18px; }
            .btn { width: 100%; padding: 14px 24px; background: #42b883; color: #0b1326; border: none; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; cursor: pointer; transition: all .15s; margin-top: 24px; border-radius: 4px; }
            .btn:hover { background: #69dca4; }
            .btn:disabled { opacity: .4; cursor: not-allowed; }
            .btn.secondary { background: transparent; border: 1px solid #1e2d4a; color: #8899b4; }
            .btn.secondary:hover { border-color: #42b883; color: #42b883; }
            .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .grid-2 label { margin-top: 0; }
            code { background: #1a2744; padding: 1px 5px; font-size: 13px; border-radius: 3px; color: #42b883; }
            .section-title { font-size: 15px; font-weight: 600; margin: 24px 0 4px; letter-spacing: -.01em; color: #c8d6f0; }
            ul { font-size: 13px; color: #8899b4; line-height: 1.8; padding-left: 18px; margin: 12px 0; }
            .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #1e2d4a; font-size: 12px; color: #4a5a78; text-align: center; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="step-bar"><div class="step-fill" style="width:<?= $pct ?>%"></div></div>
            <div class="step-indicator">Paso <?= $step ?> de <?= $totalSteps ?> — <?= htmlspecialchars($stepTitle) ?></div>
            <h1><?= htmlspecialchars($title) ?></h1>
    <?php
}

function renderFooter(): void
{
    ?>
            <div class="footer">Vunotek Installer v<?= INSTALLER_VERSION ?></div>
        </div>
    </body>
    </html>
    <?php
}

function renderAlreadyInstalled(): void
{
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ya Instalado — Vunotek</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #0b1326; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
            .card { background: #111b30; border: 1px solid #1e2d4a; box-shadow: 0 8px 32px rgba(0,0,0,.3); padding: 48px 40px; max-width: 480px; width: 100%; margin: 24px; text-align: center; }
            h1 { font-size: 24px; font-weight: 500; letter-spacing: -.01em; margin-bottom: 12px; color: #fff; }
            p { font-size: 14px; line-height: 1.6; color: #8899b4; margin-bottom: 24px; }
            .badge { display: inline-block; background: rgba(66,184,131,.15); color: #42b883; font-size: 11px; font-weight: 600; letter-spacing: .08em; padding: 6px 14px; text-transform: uppercase; border-radius: 20px; }
            a { color: #42b883; text-decoration: none; font-weight: 500; }
            a:hover { text-decoration: underline; }
            code { background: #1a2744; padding: 1px 5px; font-size: 13px; border-radius: 3px; color: #42b883; }
            .lock-note { font-size: 12px; color: #4a5a78; margin-top: 16px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="badge">✓ Instalado</div>
            <h1>Vunotek API ya está configurada</h1>
            <p>El instalador ya fue ejecutado. Si necesitas reinstalar, eliminá <code>.installer-lock</code> del servidor y volvé a acceder.</p>
            <p><a href="/admin/login">Ir al panel administrador →</a></p>
            <div class="lock-note">o eliminá <code>install.php</code> después de la instalación.</div>
        </div>
    </body>
    </html>
    <?php
}
