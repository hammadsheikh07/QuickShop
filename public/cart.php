<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Config\Database;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;

// Initialize services
$db = Database::getConnection();
$cartRepo = new CartRepository($db);
$productRepo = new ProductRepository($db);
$cartService = new CartService($cartRepo, $productRepo);

// Get session ID
$sessionId = getCartSessionId();

// Handle cart actions
$action = $_GET['action'] ?? null;
$cartItemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update' && $cartItemId > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        try {
            $cartService->updateQuantity($sessionId, $cartItemId, $quantity);
            $message = 'Cart updated successfully';
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'remove' && $cartItemId > 0) {
        try {
            $cartService->removeFromCart($sessionId, $cartItemId);
            $message = 'Item removed from cart';
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Redirect to avoid resubmission
    header('Location: cart.php' . ($message ? '?message=' . urlencode($message) : '') . ($error ? '?error=' . urlencode($error) : ''));
    exit;
}

// Fetch cart data
try {
    $cartItems = $cartService->getCart($sessionId);
    $cartTotal = $cartService->getCartTotal($sessionId);
    $cartCount = $cartService->getCartCount($sessionId);
    $hasItems = !empty($cartItems);
} catch (\Exception $e) {
    $error = 'Failed to load cart: ' . $e->getMessage();
    $cartItems = [];
    $cartTotal = 0;
    $cartCount = 0;
    $hasItems = false;
}

// Get messages from query string
$message = $_GET['message'] ?? null;
$error = $_GET['error'] ?? $error ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - QuickShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Shopping Cart</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert-message alert-success">
                <?php echo escapeHtml($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-message alert-error">
                <?php echo escapeHtml($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$hasItems): ?>
            <div class="empty-state">
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="index.php" class="btn btn-primary btn-margin-top">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $cartItem): 
                        $product = $cartItem->getProduct();
                        if (!$product) continue;
                        $subtotal = $product->getPrice() * $cartItem->getQuantity();
                    ?>
                        <div class="cart-item" data-cart-item-id="<?php echo $cartItem->getId(); ?>">
                            <div class="cart-item-image" style="background-color: <?php echo getProductColor($product->getName()); ?>;">
                                <span class="product-initials"><?php echo getProductInitials($product->getName()); ?></span>
                            </div>
                            <div class="cart-item-details">
                                <h3><a href="product.php?id=<?php echo $product->getId(); ?>"><?php echo escapeHtml($product->getName()); ?></a></h3>
                                <p class="cart-item-description"><?php echo escapeHtml($product->getDescription() ?: ''); ?></p>
                                <div class="cart-item-price"><?php echo formatPriceDisplay($product->getPrice()); ?> each</div>
                            </div>
                            <div class="cart-item-quantity">
                                <form method="POST" action="cart.php?action=update&id=<?php echo $cartItem->getId(); ?>" class="quantity-form">
                                    <div class="quantity-selector">
                                        <button 
                                            type="button" 
                                            class="quantity-btn quantity-decrease" 
                                            onclick="decreaseQuantity(<?php echo $cartItem->getId(); ?>, <?php echo $cartItem->getQuantity(); ?>, <?php echo $product->getStock(); ?>)"
                                            <?php echo $cartItem->getQuantity() <= 1 ? 'disabled' : ''; ?>
                                        >−</button>
                                        <input 
                                            type="number" 
                                            name="quantity"
                                            class="quantity-input" 
                                            value="<?php echo $cartItem->getQuantity(); ?>" 
                                            min="1" 
                                            max="<?php echo $product->getStock(); ?>"
                                            onchange="this.form.submit()"
                                        >
                                        <button 
                                            type="button" 
                                            class="quantity-btn quantity-increase" 
                                            onclick="increaseQuantity(<?php echo $cartItem->getId(); ?>, <?php echo $cartItem->getQuantity(); ?>, <?php echo $product->getStock(); ?>)"
                                            <?php echo $cartItem->getQuantity() >= $product->getStock() ? 'disabled' : ''; ?>
                                        >+</button>
                                    </div>
                                    <div class="cart-item-stock"><?php echo escapeHtml(getStockText($product->getStock())); ?></div>
                                </form>
                            </div>
                            <div class="cart-item-subtotal">
                                <div class="cart-item-subtotal-amount"><?php echo formatPriceDisplay($subtotal); ?></div>
                                <form method="POST" action="cart.php?action=remove&id=<?php echo $cartItem->getId(); ?>" class="inline-form" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                    <button type="submit" class="cart-item-remove" title="Remove item">×</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <div class="cart-summary-header">
                        <h2>Order Summary</h2>
                    </div>
                    <div class="cart-summary-details">
                        <div class="cart-summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPriceDisplay($cartTotal); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="cart-summary-row cart-summary-total">
                            <span>Total:</span>
                            <span><?php echo formatPriceDisplay($cartTotal); ?></span>
                        </div>
                    </div>
                    <div class="cart-summary-actions">
                        <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/api-config.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/cart.js"></script>
</body>
</html>
