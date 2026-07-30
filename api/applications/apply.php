<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

rateLimit('apply', 5, 3600);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido', 405);
}

use App\Controllers\CareerController;

$controller = new CareerController();
$controller->apply();
