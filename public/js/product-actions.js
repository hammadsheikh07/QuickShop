/**
 * Product Actions - Add to Cart, Buy Now, and Quantity Management
 * Uses the generic notification system
 */

// Quantity management functions
function increaseQuantity(maxStock) {
    const input = document.getElementById('product-quantity');
    if (!input) return;
    
    let currentValue = parseInt(input.value) || 1;
    if (currentValue < maxStock) {
        input.value = currentValue + 1;
    } else {
        if (window.notifications) {
            window.notifications.warning(`Maximum ${maxStock} items available`);
        }
    }
}

function decreaseQuantity() {
    const input = document.getElementById('product-quantity');
    if (!input) return;
    
    let currentValue = parseInt(input.value) || 1;
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function validateQuantity(maxStock) {
    const input = document.getElementById('product-quantity');
    if (!input) return;
    
    let value = parseInt(input.value) || 1;
    
    if (value < 1) {
        value = 1;
    } else if (value > maxStock) {
        value = maxStock;
        if (window.notifications) {
            window.notifications.warning(`Maximum ${maxStock} items available`);
        }
    }
    
    input.value = value;
}

function getQuantity() {
    const input = document.getElementById('product-quantity');
    if (!input) return 1;
    
    const quantity = parseInt(input.value) || 1;
    return Math.max(1, quantity);
}

// Quick add to cart from product card
function quickAddToCart(productId, productName) {
    if (typeof addToCart === 'function') {
        // Suppress default notification and show custom one with product name
        addToCart(productId, 1, false);
        // Ensure badge is updated (in case updateCartBadge wasn't called)
        if (typeof updateCartBadge === 'function') {
            updateCartBadge();
        }
        if (window.notifications) {
            window.notifications.success(`"${productName}" added to cart!`);
        }
    } else {
        // Fallback
        const cart = JSON.parse(localStorage.getItem('quickshop_cart') || '[]');
        const existingItem = cart.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ productId, quantity: 1 });
        }
        
        localStorage.setItem('quickshop_cart', JSON.stringify(cart));
        
        // Update cart badge
        if (typeof updateCartBadge === 'function') {
            updateCartBadge();
        } else {
            // Fallback badge update
            const badges = document.querySelectorAll('.cart-badge');
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        }
        
        if (window.notifications) {
            window.notifications.success(`"${productName}" added to cart!`);
        } else {
            alert(`"${productName}" added to cart!`);
        }
    }
}

// Add to cart from product detail page (with quantity)
function addToCartFromDetail(productId) {
    const quantity = getQuantity();
    
    if (typeof addToCart === 'function') {
        addToCart(productId, quantity);
        // Ensure badge is updated (double-check)
        if (typeof updateCartBadge === 'function') {
            updateCartBadge();
        }
    } else {
        // Fallback if navbar.js hasn't loaded
        const cart = JSON.parse(localStorage.getItem('quickshop_cart') || '[]');
        const existingItem = cart.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({ productId, quantity });
        }
        
        localStorage.setItem('quickshop_cart', JSON.stringify(cart));
        
        // Update cart badge
        if (typeof updateCartBadge === 'function') {
            updateCartBadge();
        } else {
            // Fallback badge update
            const badges = document.querySelectorAll('.cart-badge');
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        }
        
        // Use notification system if available
        if (window.notifications) {
            window.notifications.success(`Added ${quantity} item(s) to cart!`);
        } else {
            alert(`Added ${quantity} item(s) to cart!`);
        }
    }
}

// Buy now function
function buyNow(productId) {
    const quantity = getQuantity();
    
    // Add to cart first
    if (typeof addToCart === 'function') {
        addToCart(productId, quantity);
    } else {
        addToCartFromDetail(productId);
    }
    
    // Show notification
    if (window.notifications) {
        window.notifications.info('Buy Now feature - Coming soon! This will redirect to checkout.');
    } else {
        alert('Buy Now feature - Coming soon! This will redirect to checkout.');
    }
    
    // Future: window.location.href = `checkout.php?product=${productId}&quantity=${quantity}`;
}
