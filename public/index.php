<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

// Initialize services
$db = Database::getConnection();
$repository = new ProductRepository($db);
$service = new ProductService($repository);

// Fetch products
try {
    $products = $service->listProducts();
    $hasProducts = !empty($products);
} catch (Exception $e) {
    $error = 'Failed to load products. Please make sure the database is set up.';
    $products = [];
    $hasProducts = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickShop - Product Catalog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Products</h1>
            <p class="subtitle"><?php echo count($products); ?> items available</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error">
                <?php echo escapeHtml($error); ?>
            </div>
        <?php elseif (!$hasProducts): ?>
            <div class="empty-state">
                <h2>No Products Found</h2>
                <p>There are no products available at the moment.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card-wrapper">
                        <a href="product.php?id=<?php echo $product->getId(); ?>" class="product-card">
                            <div class="product-image" style="background-color: <?php echo getProductColor($product->getName()); ?>;">
                                <span class="product-initials"><?php echo getProductInitials($product->getName()); ?></span>
                                <div class="product-overlay">
                                    <span class="view-details">View Details →</span>
                                </div>
                            </div>
                            <div class="product-card-content">
                                <div class="product-name"><?php echo escapeHtml($product->getName()); ?></div>
                                <div class="product-description">
                                    <?php echo escapeHtml($product->getDescription() ?: 'No description available'); ?>
                                </div>
                                <div class="product-footer">
                                    <div class="product-price">$<?php echo formatPrice($product->getPrice()); ?></div>
                                    <div class="product-stock <?php echo getStockClass($product->getStock()); ?>">
                                        <?php echo escapeHtml(getStockText($product->getStock())); ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php if ($product->getStock() > 0): ?>
                            <button 
                                class="product-quick-add" 
                                onclick="event.stopPropagation(); event.preventDefault(); quickAddToCart(<?php echo $product->getId(); ?>, '<?php echo escapeHtml($product->getName()); ?>');"
                                title="Quick Add to Cart"
                                aria-label="Add to Cart"
                            >
                                <span class="quick-add-icon">+</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/api-config.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/product-actions.js"></script>
</body>
</html>
