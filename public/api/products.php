<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use App\Controllers\ProductController;

try {
    $db = Database::getConnection();
    $repository = new ProductRepository($db);
    $service = new ProductService($repository);
    $controller = new ProductController($service);

    $controller->handleRequest();
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

