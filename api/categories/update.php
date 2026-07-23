<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonError('Método no permitido', 405);
}

requireAuth();

use App\Controllers\CategoryController;

$controller = new CategoryController();
$controller->update();
