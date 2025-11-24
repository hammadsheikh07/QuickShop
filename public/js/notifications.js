/**
 * Generic Notification Component
 * Reusable notification system for the entire application
 */

class NotificationManager {
    constructor() {
        this.container = null;
        this.initialized = false;
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeContainer());
        } else {
            this.initializeContainer();
        }
    }

    initializeContainer() {
        if (this.initialized) return;
        
        // Check if container already exists
        let existingContainer = document.getElementById('notification-container');
        
        if (existingContainer) {
            // If container exists, check if it's already in the body (not nested)
            if (existingContainer.parentElement === document.body) {
                this.container = existingContainer;
            } else {
                // If it's nested, remove it and create a new one
                existingContainer.remove();
                this.createContainer();
            }
        } else {
            this.createContainer();
        }
        
        this.initialized = true;
    }

    createContainer() {
        // Ensure we're appending to body, not any other container
        if (!document.body) {
            // If body doesn't exist yet, wait a bit and try again
            setTimeout(() => this.createContainer(), 10);
            return;
        }
        
        // Remove ALL existing containers first (even if properly placed, we'll recreate)
        const allContainers = document.querySelectorAll('#notification-container');
        allContainers.forEach(container => container.remove());
        
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'notification-container';
        
        // Force append to body, ensuring it's the last child
        document.body.appendChild(this.container);
        
        // Double-check it's in the right place
        if (this.container.parentElement !== document.body) {
            // If somehow it's still in the wrong place, move it
            this.container.remove();
            document.body.appendChild(this.container);
        }
    }
    
    ensureContainer() {
        // Check if container exists and is properly placed
        if (!this.container || !document.body.contains(this.container)) {
            this.initializeContainer();
            return;
        }
        
        // If container is not directly in body, move it
        if (this.container.parentElement !== document.body) {
            const parent = this.container.parentElement;
            parent.removeChild(this.container);
            document.body.appendChild(this.container);
        }
    }

    /**
     * Show a notification
     * @param {string} message - The message to display
     * @param {string} type - Type of notification: 'success', 'error', 'info', 'warning'
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    show(message, type = 'success', duration = 3000) {
        // Ensure container exists and is properly attached
        this.ensureContainer();
        
        if (!this.container) {
            console.error('Notification container not available');
            return null;
        }
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Add icon based on type
        const icons = {
            success: '✓',
            error: '✕',
            info: 'ℹ',
            warning: '⚠'
        };
        
        notification.innerHTML = `
            <span class="notification-icon">${icons[type] || icons.success}</span>
            <span class="notification-message">${this.escapeHtml(message)}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        this.container.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('notification-show');
        }, 10);
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }
        
        return notification;
    }

    /**
     * Show success notification
     */
    success(message, duration = 3000) {
        return this.show(message, 'success', duration);
    }

    /**
     * Show error notification
     */
    error(message, duration = 4000) {
        return this.show(message, 'error', duration);
    }

    /**
     * Show info notification
     */
    info(message, duration = 3000) {
        return this.show(message, 'info', duration);
    }

    /**
     * Show warning notification
     */
    warning(message, duration = 3500) {
        return this.show(message, 'warning', duration);
    }

    /**
     * Remove a notification
     */
    remove(notification) {
        if (notification && notification.parentElement) {
            notification.classList.add('notification-hide');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }

    /**
     * Clear all notifications
     */
    clear() {
        const notifications = this.container.querySelectorAll('.notification');
        notifications.forEach(notification => this.remove(notification));
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Create global instance only if it doesn't exist
// This prevents multiple instances from being created if the script is loaded multiple times
if (!window.notifications) {
    window.notifications = new NotificationManager();
} else {
    // If instance exists, ensure container is properly placed
    if (window.notifications.container && window.notifications.container.parentElement !== document.body) {
        const container = window.notifications.container;
        container.remove();
        document.body.appendChild(container);
    }
}

