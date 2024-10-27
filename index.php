<?php

require_once __DIR__ . '/autoload.php';

use Core\Router;

$router = new Router();

// Dispatch the incoming POST request
// Requests must be made to /index.php?controller=...&action=...
$router->dispatch();