/**
 * Checkout page JavaScript - handles form submission
 * 
 * Note: Requires api-config.js to be loaded first for API_BASE
 */

/**
 * Handle checkout form submission with AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
    // Update cart badge
    if (typeof updateCartBadge === 'function') {
        updateCartBadge();
    }

    // Handle form submission
    const form = document.getElementById('checkout-form');
    if (!form) return;

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const submitBtn = document.getElementById('submit-btn');
        if (!submitBtn) return;
        
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        const formData = new FormData(form);
        const customerData = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone') || null,
            address: formData.get('address')
        };

        try {
            const response = await fetch(`${window.API_BASE}/checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(customerData)
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create order');
            }
            
            const order = await response.json();
            
            // Redirect to order confirmation
            window.location.href = `order-confirmation.php?order_id=${order.id}`;
        } catch (error) {
            console.error('Error creating order:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            if (window.notifications) {
                window.notifications.error(error.message || 'Failed to place order');
            } else {
                alert('Error: ' + error.message);
            }
        }
    });
});
