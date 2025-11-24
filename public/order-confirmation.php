<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Config\Database;
use App\Repositories\OrderRepository;
use App\Services\CheckoutService;

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    header('Location: index.php');
    exit;
}

// Initialize services
$db = Database::getConnection();
$orderRepo = new OrderRepository($db);
$cartRepo = new \App\Repositories\CartRepository($db);
$productRepo = new \App\Repositories\ProductRepository($db);
$service = new CheckoutService($orderRepo, $cartRepo, $productRepo);

// Fetch order
$order = $service->getOrder($orderId);

if (!$order) {
    http_response_code(404);
    $error = 'Order not found';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - QuickShop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error">
                <h2>Order Not Found</h2>
                <p><?php echo escapeHtml($error); ?></p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Browse Products</a>
            </div>
        <?php elseif ($order): ?>
            <div class="order-confirmation">
                <div class="order-confirmation-header">
                    <h1>Order Confirmed!</h1>
                    <p class="order-number">Order #<?php echo $order->getId(); ?></p>
                </div>

                <div class="order-confirmation-content">
                    <div class="order-details-section">
                        <h2>Order Details</h2>
                        <div class="order-info">
                            <div class="order-info-row">
                                <span>Order Date:</span>
                                <span><?php echo date('F j, Y g:i A', strtotime($order->getCreatedAt())); ?></span>
                            </div>
                            <div class="order-info-row">
                                <span>Status:</span>
                                <span class="order-status"><?php echo escapeHtml(ucfirst($order->getStatus())); ?></span>
                            </div>
                            <div class="order-info-row">
                                <span>Total Amount:</span>
                                <span class="order-total">$<?php echo formatPrice($order->getTotalAmount()); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="customer-details-section">
                        <h2>Shipping Information</h2>
                        <div class="customer-info">
                            <p><strong>Name:</strong> <?php echo escapeHtml($order->getCustomerName()); ?></p>
                            <p><strong>Email:</strong> <?php echo escapeHtml($order->getCustomerEmail()); ?></p>
                            <?php if ($order->getCustomerPhone()): ?>
                                <p><strong>Phone:</strong> <?php echo escapeHtml($order->getCustomerPhone()); ?></p>
                            <?php endif; ?>
                            <p><strong>Address:</strong><br><?php echo nl2br(escapeHtml($order->getShippingAddress())); ?></p>
                        </div>
                    </div>

                    <div class="order-items-section">
                        <h2>Order Items</h2>
                        <div class="order-items-list">
                            <?php foreach ($order->getItems() as $item): ?>
                                <div class="order-item">
                                    <div class="order-item-details">
                                        <h3><?php echo escapeHtml($item['product_name']); ?></h3>
                                        <p>Quantity: <?php echo $item['quantity']; ?> × $<?php echo formatPrice($item['product_price']); ?></p>
                                    </div>
                                    <div class="order-item-subtotal">
                                        $<?php echo formatPrice($item['subtotal']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="order-confirmation-actions">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/api-config.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/navbar.js"></script>
</body>
</html>

