<?php
declare(strict_types=1);

/**
 * Bootstrap compartido para todos los endpoints SOA.
 * Cada entry point require_once este archivo.
 */

config(require __DIR__ . '/config.php');
$config = config();

// --- Composer Autoloader (PHPMailer, etc.) ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Autoloader PSR-4 ---
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// --- CORS ---
setCorsHeaders();

// --- Helpers ---
function config(?array $merge = null): array
{
    static $config = null;

    if ($merge !== null) {
        $config = $merge;
        return $config;
    }

    return $config ?? [];
}

function setCorsHeaders(): void
{
    $config = config();
    $allowedOrigins = $config['app']['allowed_origins'] ?? [];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $code = 400, array $errors = []): never
{
    $response = ['success' => false, 'message' => $message];
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    jsonResponse($response, $code);
}

function jsonSuccess(mixed $data = null, string $message = '', int $code = 200): never
{
    $response = ['success' => true];
    if ($message !== '') {
        $response['message'] = $message;
    }
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response, $code);
}

function requireAuth(): array
{
    $token = $_COOKIE['admin_token'] ?? '';

    if ($token === '') {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);
        }
    }

    if ($token === '') {
        jsonError('Token de autenticación requerido', 401);
    }

    $payload = verifyJwt($token);

    if ($payload === null) {
        jsonError('Token inválido o expirado', 401);
    }

    return $payload;
}

function createJwt(array $payload): string
{
    $config = config();
    $secret = $config['jwt']['secret'];
    $expiresIn = $config['jwt']['expires_in'] ?? 86400;
    $issuer = $config['jwt']['issuer'] ?? 'vunotek.com';

    $now = time();

    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

    $claims = array_merge($payload, [
        'iss' => $issuer,
        'iat' => $now,
        'exp' => $now + $expiresIn,
    ]);

    $body = base64UrlEncode(json_encode($claims));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$body", $secret, true));

    return "$header.$body.$signature";
}

function verifyJwt(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$header, $body, $signature] = $parts;
    $config = config();
    $secret = $config['jwt']['secret'];

    $expectedSig = base64UrlEncode(hash_hmac('sha256', "$header.$body", $secret, true));

    if (!hash_equals($expectedSig, $signature)) {
        return null;
    }

    $claims = json_decode(base64_decode(strtr($body, '-_', '+/')), true);

    if ($claims === null || !isset($claims['exp']) || $claims['exp'] < time()) {
        return null;
    }

    return $claims;
}

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        jsonError('Datos JSON inválidos');
    }

    return $data;
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// --- Rate Limiting (file-based) ---

function getClientIp(): string
{
    $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rateLimit(string $key, int $maxAttempts, int $windowSeconds): void
{
    $ip = getClientIp();
    $dir = sys_get_temp_dir() . '/vunotek-ratelimit';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$key}_{$ip}") . '.json';

    $now = time();
    $attempts = [];

    if (file_exists($file)) {
        $raw = file_get_contents($file);
        $attempts = json_decode($raw, true) ?: [];
    }

    // Clean old attempts outside the window
    $attempts = array_filter($attempts, fn(int $ts) => $ts > $now - $windowSeconds);
    $attempts = array_values($attempts);

    if (count($attempts) >= $maxAttempts) {
        $retryAfter = $attempts[0] + $windowSeconds - $now;
        header('Retry-After: ' . $retryAfter);
        apiLog('WARNING', "Rate limit exceeded", [
            'key' => $key,
            'ip' => $ip,
            'attempts' => count($attempts),
            'max' => $maxAttempts,
        ]);
        jsonError('Demasiadas solicitudes. Intentá de nuevo en ' . ceil($retryAfter / 60) . ' minutos.', 429);
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts), LOCK_EX);
}

function rateLimitCheck(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $ip = getClientIp();
    $dir = sys_get_temp_dir() . '/vunotek-ratelimit';
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$key}_{$ip}") . '.json';

    if (!file_exists($file)) return true;

    $now = time();
    $attempts = json_decode(file_get_contents($file), true) ?: [];
    $attempts = array_filter($attempts, fn(int $ts) => $ts > $now - $windowSeconds);

    return count($attempts) < $maxAttempts;
}

function rateLimitIncrement(string $key): void
{
    $ip = getClientIp();
    $dir = sys_get_temp_dir() . '/vunotek-ratelimit';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', "{$key}_{$ip}") . '.json';

    $attempts = [];
    if (file_exists($file)) {
        $attempts = json_decode(file_get_contents($file), true) ?: [];
    }
    $attempts[] = time();
    file_put_contents($file, json_encode($attempts), LOCK_EX);
}

// --- Logging ---

function apiLog(string $level, string $message, array $context = []): void
{
    $dir = sys_get_temp_dir() . '/vunotek-api-log';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $date = date('Y-m-d');
    $file = $dir . "/api-{$date}.log";
    $time = date('Y-m-d H:i:s');
    $ip = getClientIp();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? '-';

    $line = "[{$time}] [{$level}] [{$ip}] {$method} {$uri} — {$message}";
    if (!empty($context)) {
        $line .= ' ' . json_encode($context);
    }
    $line .= "\n";

    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}
