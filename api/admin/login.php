<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido', 405);
}

rateLimit('login', 3, 900); // 3 intentos / 15 min

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->login();
