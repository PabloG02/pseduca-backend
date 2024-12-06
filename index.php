<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/loadconfig.php';

use Core\Router;

// Add CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Set the content type to JSON
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$router = new Router();

// Dispatch the incoming POST request
// Requests must be made to /index.php?controller=...&action=...
$router->dispatch();
