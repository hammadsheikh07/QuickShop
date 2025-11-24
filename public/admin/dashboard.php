<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/includes/helpers.php';
require_once __DIR__ . '/../../public/includes/admin-helpers.php';

use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

requireAdminAuth();

$db = Database::getConnection();
$repository = new ProductRepository($db);
$service = new ProductService($repository);

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

try {
    $products = $service->listAllProductsIncludingDeleted();
} catch (Exception $e) {
    $error = 'Failed to load products: ' . $e->getMessage();
    $products = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuickShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>Product Management</h1>
            <div class="admin-header-actions">
                <span class="admin-username">Logged in as: <?php echo escapeHtml(getCurrentAdminUsername()); ?></span>
                <a href="product-form.php" class="admin-button">Add New Product</a>
                <a href="logout.php" class="admin-button admin-button-secondary">Logout</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="admin-success-message">
                <?php echo escapeHtml($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-error-message">
                <?php echo escapeHtml($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h2>No Products Found</h2>
                <p>Get started by adding your first product.</p>
                <a href="product-form.php" class="admin-button">Add Product</a>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $isDeleted = $repository->isDeleted($product->getId());
                        ?>
                        <tr>
                            <td><?php echo $product->getId(); ?></td>
                            <td><?php echo escapeHtml($product->getName()); ?></td>
                            <td><?php echo escapeHtml(substr($product->getDescription(), 0, 50)) . (strlen($product->getDescription()) > 50 ? '...' : ''); ?></td>
                            <td>$<?php echo formatPrice($product->getPrice()); ?></td>
                            <td><?php echo $product->getStock(); ?></td>
                            <td>
                                <?php if ($isDeleted): ?>
                                    <span class="admin-deleted-badge">Deleted</span>
                                <?php else: ?>
                                    <span>Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="product-form.php?id=<?php echo $product->getId(); ?>" class="admin-edit-button">Edit</a>
                                    <?php if ($isDeleted): ?>
                                        <form method="POST" action="restore-product.php">
                                            <input type="hidden" name="id" value="<?php echo $product->getId(); ?>">
                                            <button type="submit" class="admin-button admin-button-secondary">Restore</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="delete-product.php" onsubmit="return confirm('Are you sure you want to delete this product? It will be hidden from customers but order history will be preserved.');">
                                            <input type="hidden" name="id" value="<?php echo $product->getId(); ?>">
                                            <button type="submit" class="admin-delete-button">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

