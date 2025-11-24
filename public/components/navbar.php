<!-- Navbar Component -->
<div class="top-banner">
    Free delivery on orders over $60 | <a href="#">Free returns</a> | <a href="#">Store locator</a>
</div>
<nav class="navbar">
    <div class="navbar-top">
        <a href="index.php" class="navbar-brand">
            QuickShop
        </a>
        <div class="navbar-actions">
            <a href="#">Help</a>
            <a href="#">Find a store</a>
            <a href="#">Sign in</a>
            <a href="#" class="cart-icon cart-link" title="Shopping bag">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M5 7h10l-1 8H6L5 7z"/>
                    <path d="M7 7V4a3 3 0 0 1 6 0v3"/>
                </svg>
                <span class="cart-badge hidden">0</span>
            </a>
        </div>
    </div>
    <div class="navbar-main">
        <div class="navbar-links">
            <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a>
            <a href="#">Products</a>
            <a href="#">Offers</a>
            <a href="#">New Arrivals</a>
            <a href="cart.php" class="cart-link">Cart</a>
        </div>
    </div>
</nav>

