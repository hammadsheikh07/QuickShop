<!-- Navbar Component -->
<?php
// Initialize session early if not already started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Check if we're in admin section
$isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '\\admin\\') !== false;

// Check if admin helpers are available
$isAdminLoggedIn = false;
$adminUsername = null;

if (file_exists(__DIR__ . '/../includes/admin-helpers.php')) {
    require_once __DIR__ . '/../includes/admin-helpers.php';
    $isAdminLoggedIn = isAdminAuthenticated();
    $adminUsername = getCurrentAdminUsername();
}

// Determine base path for links
$basePath = $isAdminPage ? '../' : '';
?>
<?php if (!$isAdminPage): ?>
<div class="top-banner">
    Free delivery on orders over $60 | <a href="#">Free returns</a> | <a href="#">Store locator</a>
</div>
<?php endif; ?>
<nav class="navbar">
    <div class="navbar-top">
        <a href="<?php echo $basePath; ?>index.php" class="navbar-brand">
            QuickShop
        </a>
        <div class="navbar-actions">
            <?php if ($isAdminPage): ?>
                <?php if ($isAdminLoggedIn): ?>
                    <span class="admin-username"><?php echo htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Sign in</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="#">Help</a>
                <a href="#">Find a store</a>
                <?php if ($isAdminLoggedIn): ?>
                    <a href="admin/dashboard.php">Admin Dashboard</a>
                    <a href="admin/logout.php">Logout (<?php echo htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8'); ?>)</a>
                <?php else: ?>
                    <a href="admin/login.php">Sign in</a>
                <?php endif; ?>
                <a href="#" class="cart-icon cart-link" title="Shopping bag">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M5 7h10l-1 8H6L5 7z"/>
                        <path d="M7 7V4a3 3 0 0 1 6 0v3"/>
                    </svg>
                    <span class="cart-badge hidden">0</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!$isAdminPage): ?>
    <div class="navbar-main">
        <div class="navbar-links">
            <a href="<?php echo $basePath; ?>index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a>
            <a href="#">Products</a>
            <a href="#">Offers</a>
            <a href="#">New Arrivals</a>
            <a href="<?php echo $basePath; ?>cart.php" class="cart-link">Cart</a>
        </div>
    </div>
    <?php endif; ?>
</nav>

