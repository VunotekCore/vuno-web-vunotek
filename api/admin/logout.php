<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

setcookie('admin_token', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly'  => true,
    'samesite' => 'Lax',
]);

jsonSuccess(['message' => 'Sesión cerrada']);
