/**
 * Navbar JavaScript - handles cart badge and cart actions
 * 
 * Note: Requires api-config.js to be loaded first for API_BASE
 */

let cartCountCache = null;

async function fetchCartCount() {
    try {
        const response = await fetch(`${window.API_BASE}/cart.php`);
        if (!response.ok) return 0;
        const data = await response.json();
        return data.count || 0;
    } catch (error) {
        console.error('Error fetching cart count:', error);
        return 0;
    }
}

async function addToCart(productId, quantity = 1, showNotification = true) {
    try {
        const response = await fetch(`${window.API_BASE}/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to add to cart');
        }
        
        cartCountCache = null; // Invalidate cache
        await updateCartBadge();
        
        // Use generic notification system (only if not suppressed)
        if (showNotification && window.notifications) {
            window.notifications.success('Product added to cart!');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        if (window.notifications) {
            window.notifications.error(error.message || 'Failed to add product to cart');
        }
        throw error;
    }
}

function removeFromCart(productId) {
    // This function is kept for backward compatibility but may not work with backend
    // The cart page handles removal via cart item ID
    console.warn('removeFromCart by productId is deprecated. Use cart item ID instead.');
}

async function getCartCount() {
    if (cartCountCache !== null) {
        return cartCountCache;
    }
    cartCountCache = await fetchCartCount();
    return cartCountCache;
}

async function updateCartBadge() {
    // Update all cart badges on the page
    const badges = document.querySelectorAll('.cart-badge');
    const count = await getCartCount();
    cartCountCache = count;
    
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
    window.location.href = 'cart.php';
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
