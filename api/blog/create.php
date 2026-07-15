<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

requireAuth();

use App\Controllers\BlogController;

$controller = new BlogController();
$controller->create();
