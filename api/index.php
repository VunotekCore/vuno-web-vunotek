<?php
declare(strict_types=1);

if (file_exists(__DIR__ . '/config.php')) {
    http_response_code(404);
    exit('Not found');
}

header('Location: install.php');
exit;
