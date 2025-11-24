<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Config\Database;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Repositories\OrderRepository;

// Initialize services
$db = Database::getConnection();
$cartRepo = new CartRepository($db);
$productRepo = new ProductRepository($db);
$cartService = new CartService($cartRepo, $productRepo);
$orderRepo = new OrderRepository($db);
$checkoutService = new CheckoutService($orderRepo, $cartRepo, $productRepo);

// Get session ID
$sessionId = getCartSessionId();

// Handle form submission
$error = null;
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? ''
    ];

    try {
        $order = $checkoutService->createOrder($sessionId, $formData);
        header('Location: order-confirmation.php?order_id=' . $order->getId());
        exit;
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch cart data
try {
    $cartItems = $cartService->getCart($sessionId);
    $cartTotal = $cartService->getCartTotal($sessionId);
    $hasItems = !empty($cartItems);
} catch (\Exception $e) {
    $error = 'Failed to load cart: ' . $e->getMessage();
    $cartItems = [];
    $cartTotal = 0;
    $hasItems = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - QuickShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Checkout</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert-message alert-error">
                <?php echo escapeHtml($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$hasItems): ?>
            <div class="empty-state">
                <h2>Your cart is empty</h2>
                <p>Add some products to checkout!</p>
                <a href="index.php" class="btn btn-primary btn-margin-top">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="checkout-content">
                <div class="checkout-form-section">
                    <h2>Shipping Information</h2>
                    <form method="POST" action="checkout.php" id="checkout-form">
                        <div class="form-group">
                            <label for="customer-name">Full Name *</label>
                            <input 
                                type="text" 
                                id="customer-name" 
                                name="name" 
                                required 
                                placeholder="John Doe"
                                value="<?php echo escapeHtml($formData['name'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="customer-email">Email Address *</label>
                            <input 
                                type="email" 
                                id="customer-email" 
                                name="email" 
                                required 
                                placeholder="john@example.com"
                                value="<?php echo escapeHtml($formData['email'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="customer-phone">Phone Number</label>
                            <input 
                                type="tel" 
                                id="customer-phone" 
                                name="phone" 
                                placeholder="+1 (555) 123-4567"
                                value="<?php echo escapeHtml($formData['phone'] ?? ''); ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label for="shipping-address">Shipping Address *</label>
                            <textarea 
                                id="shipping-address" 
                                name="address" 
                                required 
                                rows="4"
                                placeholder="123 Main St, City, State, ZIP Code"
                            ><?php echo escapeHtml($formData['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-actions">
                            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
                <div class="checkout-summary-section">
                    <h2>Order Summary</h2>
                    <div class="checkout-items">
                        <?php foreach ($cartItems as $cartItem): 
                            $product = $cartItem->getProduct();
                            if (!$product) continue;
                            $subtotal = $product->getPrice() * $cartItem->getQuantity();
                        ?>
                            <div class="checkout-item">
                                <div class="checkout-item-image" style="background-color: <?php echo getProductColor($product->getName()); ?>;">
                                    <span class="product-initials"><?php echo getProductInitials($product->getName()); ?></span>
                                </div>
                                <div class="checkout-item-details">
                                    <h3><?php echo escapeHtml($product->getName()); ?></h3>
                                    <div class="checkout-item-meta">
                                        <span>Quantity: <?php echo $cartItem->getQuantity(); ?></span>
                                        <span><?php echo formatPriceDisplay($product->getPrice()); ?> each</span>
                                    </div>
                                </div>
                                <div class="checkout-item-subtotal">
                                    <?php echo formatPriceDisplay($subtotal); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="checkout-summary">
                        <div class="checkout-summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPriceDisplay($cartTotal); ?></span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="checkout-summary-row checkout-summary-total">
                            <span>Total:</span>
                            <span><?php echo formatPriceDisplay($cartTotal); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/api-config.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/checkout.js"></script>
</body>
</html>
