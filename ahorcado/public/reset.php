<?php

require_once __DIR__ . '/src/Storage.php';

use Ahorcado\Storage;

$storage = new Storage();
$storage->reset();

header('Location: index.php');
exit;
