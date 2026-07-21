<?php
declare(strict_types=1);

/**
 * Bootstrap compartido para todos los endpoints SOA.
 * Cada entry point require_once este archivo.
 */

config(require __DIR__ . '/config.php');
$config = config();

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
