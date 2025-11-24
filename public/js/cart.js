/**
 * Cart page JavaScript - handles quantity updates via AJAX
 * 
 * Note: Requires api-config.js to be loaded first for API_BASE
 */

/**
 * Update cart item quantity via AJAX
 */
async function updateCartQuantity(cartItemId, quantity) {
    try {
        const response = await fetch(`${window.API_BASE}/cart.php/${cartItemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quantity })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to update cart');
        }
        
        // Reload page to show updated cart
        window.location.reload();
    } catch (error) {
        console.error('Error updating cart:', error);
        if (window.notifications) {
            window.notifications.error(error.message || 'Failed to update cart');
        } else {
            alert('Error: ' + error.message);
        }
    }
}

/**
 * Decrease quantity - defined on window immediately for inline handlers
 */
window.decreaseQuantity = function(cartItemId, currentQuantity, maxStock) {
    if (currentQuantity > 1) {
        updateCartQuantity(cartItemId, currentQuantity - 1);
    }
};

/**
 * Increase quantity - defined on window immediately for inline handlers
 */
window.increaseQuantity = function(cartItemId, currentQuantity, maxStock) {
    if (currentQuantity < maxStock) {
        updateCartQuantity(cartItemId, currentQuantity + 1);
    } else {
        if (window.notifications) {
            window.notifications.error(`Only ${maxStock} available in stock`);
        }
    }
};

// Also make updateCartQuantity available globally
window.updateCartQuantity = updateCartQuantity;

// Update cart badge on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }
});
