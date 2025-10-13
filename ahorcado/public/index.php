<?php
declare(strict_types=1);

use App\Presentation\Controllers\GameController;

require __DIR__ . '/../src/Infrastructure/Autoload/Autoloader.php';
\App\Infrastructure\Autoload\Autoloader::register('App\\', __DIR__ . '/../src');

$config = require __DIR__ . '/../config/config.php';

$controller = new GameController($config);
$controller->handle();
