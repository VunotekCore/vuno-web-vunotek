<?php
declare(strict_types=1);

namespace App\Traits;

trait JwtAuth
{
    use ApiResponse;

    private function getConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../config.php';
        }
        return $config;
    }

    public function requireAuth(): array
    {
        $token = $_COOKIE['admin_token'] ?? '';

        if ($token === '') {
            $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (str_starts_with($header, 'Bearer ')) {
                $token = substr($header, 7);
            }
        }

        if ($token === '') {
            $this->jsonError('Token de autenticación requerido', 401);
        }

        $payload = $this->verifyToken($token);

        if ($payload === null) {
            $this->jsonError('Token inválido o expirado', 401);
        }

        return $payload;
    }

    public function createToken(array $payload): string
    {
        $config = $this->getConfig();
        $secret = $config['jwt']['secret'];
        $expiresIn = $config['jwt']['expires_in'] ?? 86400;
        $issuer = $config['jwt']['issuer'] ?? 'vunotek.com';

        $now = time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        $claims = array_merge($payload, [
            'iss' => $issuer,
            'iat' => $now,
            'exp' => $now + $expiresIn,
        ]);

        $body = $this->base64UrlEncode(json_encode($claims));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$body", $secret, true)
        );

        return "$header.$body.$signature";
    }

    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $body, $signature] = $parts;

        $config = $this->getConfig();
        $secret = $config['jwt']['secret'];

        $expectedSig = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$body", $secret, true)
        );

        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }

        $claims = json_decode(base64_decode(strtr($body, '-_', '+/')), true);

        if ($claims === null || !isset($claims['exp'])) {
            return null;
        }

        if ($claims['exp'] < time()) {
            return null;
        }

        return $claims;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
