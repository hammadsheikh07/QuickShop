<?php

require_once __DIR__ . '/../../public/includes/session-init.php';
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

$product = null;
$isEdit = false;
$error = null;
$formData = [];

if (isset($_GET['id'])) {
    $isEdit = true;
    $productId = (int) $_GET['id'];
    $product = $repository->getByIdIncludingDeleted($productId);
    
    if (!$product) {
        header('Location: dashboard.php?error=Product not found');
        exit;
    }
    
    $formData = [
        'name' => $product->getName(),
        'description' => $product->getDescription(),
        'price' => $product->getPrice(),
        'stock' => $product->getStock()
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => $_POST['price'] ?? '',
        'stock' => (int) ($_POST['stock'] ?? 0)
    ];

    try {
        if ($isEdit) {
            $productId = (int) $_POST['id'];
            $service->updateProduct($productId, $formData);
            header('Location: dashboard.php?success=Product updated successfully');
        } else {
            $service->createProduct($formData);
            header('Location: dashboard.php?success=Product created successfully');
        }
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Product - QuickShop Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="admin-container">
        <div class="admin-form-container">
            <h2><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></h2>

            <?php if ($error): ?>
                <div class="admin-error-message">
                    <?php echo escapeHtml($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="admin-form">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo $product->getId(); ?>">
                <?php endif; ?>

                <div class="admin-form-group">
                    <label for="name">Product Name *</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        value="<?php echo escapeHtml($formData['name'] ?? ''); ?>"
                    >
                </div>

                <div class="admin-form-group">
                    <label for="description">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                    ><?php echo escapeHtml($formData['description'] ?? ''); ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="price">Price *</label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        step="0.01" 
                        min="0" 
                        required
                        value="<?php echo escapeHtml($formData['price'] ?? ''); ?>"
                    >
                </div>

                <div class="admin-form-group">
                    <label for="stock">Stock Quantity</label>
                    <input 
                        type="number" 
                        id="stock" 
                        name="stock" 
                        min="0" 
                        value="<?php echo escapeHtml($formData['stock'] ?? 0); ?>"
                    >
                </div>

                <div class="admin-form-actions">
                    <a href="dashboard.php" class="admin-button admin-button-secondary">Cancel</a>
                    <button type="submit" class="admin-button">
                        <?php echo $isEdit ? 'Update Product' : 'Create Product'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

