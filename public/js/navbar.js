const CART_KEY = 'quickshop_cart';

function getCart() {
    const cart = localStorage.getItem(CART_KEY);
    return cart ? JSON.parse(cart) : [];
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

function addToCart(productId, quantity = 1, showNotification = true) {
    const cart = getCart();
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ productId, quantity });
    }
    
    saveCart(cart);
    updateCartBadge();
    
    // Use generic notification system (only if not suppressed)
    if (showNotification && window.notifications) {
        window.notifications.success('Product added to cart!');
    }
}

function removeFromCart(productId) {
    const cart = getCart().filter(item => item.productId !== productId);
    saveCart(cart);
    updateCartBadge();
    
    if (window.notifications) {
        window.notifications.info('Product removed from cart');
    }
}

function getCartCount() {
    const cart = getCart();
    return cart.reduce((total, item) => total + item.quantity, 0);
}

function updateCartBadge() {
    // Update all cart badges on the page
    const badges = document.querySelectorAll('.cart-badge');
    const count = getCartCount();
    
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}

function showCart() {
    const cart = getCart();
    if (cart.length === 0) {
        if (window.notifications) {
            window.notifications.info('Your cart is empty!');
        } else {
            alert('Your cart is empty!');
        }
    } else {
        if (window.notifications) {
            window.notifications.info(`You have ${cart.length} item(s) in your cart. Cart page coming soon!`);
        } else {
            alert('Cart page coming soon! You have ' + cart.length + ' item(s) in your cart.');
        }
    }
}

// Initialize cart badge and cart link handlers on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update badge immediately
    updateCartBadge();
    
    // Also update after a short delay to ensure DOM is fully ready
    setTimeout(updateCartBadge, 100);
    
    // Set up cart link event listeners
    const cartLinks = document.querySelectorAll('.cart-link');
    cartLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showCart();
        });
    });
});

// Also update badge when page becomes visible (handles tab switching)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateCartBadge();
    }
});

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { addToCart, removeFromCart, getCart, getCartCount };
}
