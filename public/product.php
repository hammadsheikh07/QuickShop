<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: index.php');
    exit;
}

// Initialize services
$db = Database::getConnection();
$repository = new ProductRepository($db);
$service = new ProductService($repository);

// Fetch product
$product = $service->getProduct($productId);

if (!$product) {
    http_response_code(404);
    $error = 'Product not found';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? escapeHtml($product->getName()) . ' - QuickShop' : 'Product Not Found - QuickShop'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container">
        <a href="index.php" class="back-button">← Back to Products</a>

        <?php if (isset($error)): ?>
            <div class="error">
                <h2>Product Not Found</h2>
                <p><?php echo escapeHtml($error); ?></p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Browse Products</a>
            </div>
        <?php elseif ($product): ?>
            <div class="product-detail">
                <div class="product-detail-content">
                    <div class="product-detail-image" style="background-color: <?php echo getProductColor($product->getName()); ?>;">
                        <span class="product-initials"><?php echo getProductInitials($product->getName()); ?></span>
                    </div>
                    <div class="product-detail-info">
                        <h1><?php echo escapeHtml($product->getName()); ?></h1>
                        <div class="product-detail-price">$<?php echo formatPrice($product->getPrice()); ?></div>
                        <div class="product-detail-description">
                            <?php echo nl2br(escapeHtml($product->getDescription() ?: 'No description available')); ?>
                        </div>
                        <div class="product-detail-meta">
                            <p>
                                <strong>Stock:</strong> 
                                <span class="product-stock <?php echo getStockClass($product->getStock()); ?>">
                                    <?php echo escapeHtml(getStockText($product->getStock())); ?>
                                </span>
                            </p>
                            <p><strong>Product ID:</strong> #<?php echo $product->getId(); ?></p>
                        </div>
                        <div class="product-quantity-section">
                            <label for="product-quantity" class="quantity-label">
                                <strong>Quantity:</strong>
                            </label>
                            <div class="quantity-selector">
                                <button 
                                    type="button" 
                                    class="quantity-btn quantity-decrease" 
                                    onclick="decreaseQuantity()"
                                    <?php echo $product->getStock() === 0 ? 'disabled' : ''; ?>
                                >−</button>
                                <input 
                                    type="number" 
                                    id="product-quantity" 
                                    class="quantity-input" 
                                    value="1" 
                                    min="1" 
                                    max="<?php echo $product->getStock(); ?>"
                                    onchange="validateQuantity(<?php echo $product->getStock(); ?>)"
                                    <?php echo $product->getStock() === 0 ? 'disabled' : ''; ?>
                                >
                                <button 
                                    type="button" 
                                    class="quantity-btn quantity-increase" 
                                    onclick="increaseQuantity(<?php echo $product->getStock(); ?>)"
                                    <?php echo $product->getStock() === 0 ? 'disabled' : ''; ?>
                                >+</button>
                            </div>
                            <span class="quantity-hint">Max: <?php echo $product->getStock(); ?> available</span>
                        </div>
                        <div class="product-actions">
                            <button 
                                class="btn btn-secondary" 
                                onclick="addToCartFromDetail(<?php echo $product->getId(); ?>)"
                                <?php echo $product->getStock() === 0 ? 'disabled' : ''; ?>
                            >
                                Add to Cart
                            </button>
                            <button 
                                class="btn btn-primary" 
                                onclick="buyNow(<?php echo $product->getId(); ?>)"
                                <?php echo $product->getStock() === 0 ? 'disabled' : ''; ?>
                            >
                                Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/notifications.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/product-actions.js"></script>
</body>
</html>
