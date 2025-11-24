<?php

require_once __DIR__ . '/../../public/includes/session-init.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/includes/helpers.php';
require_once __DIR__ . '/../../public/includes/admin-helpers.php';

use App\Config\Database;
use App\Repositories\ProductRepository;

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$productId = (int) ($_POST['id'] ?? 0);

if ($productId <= 0) {
    header('Location: dashboard.php?error=Invalid product ID');
    exit;
}

$db = Database::getConnection();
$repository = new ProductRepository($db);

try {
    $repository->restore($productId);
    header('Location: dashboard.php?success=Product restored successfully');
} catch (Exception $e) {
    header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
}
exit;

