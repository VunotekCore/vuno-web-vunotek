<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido', 405);
}

setcookie('admin_token', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443),
    'httponly'  => true,
    'samesite' => 'Lax',
]);

jsonSuccess(['message' => 'Sesión cerrada']);
