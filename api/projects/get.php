<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\ProjectController;

$controller = new ProjectController();
$controller->get();
