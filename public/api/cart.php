<?php

require_once __DIR__ . '/../../public/includes/session-init.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Controllers\CartController;

try {
    $db = Database::getConnection();
    $cartRepo = new CartRepository($db);
    $productRepo = new ProductRepository($db);
    $service = new CartService($cartRepo, $productRepo);
    $controller = new CartController($service);

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

