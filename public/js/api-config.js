/**
 * Shared API Configuration
 * Centralized API base URL configuration for all JavaScript files
 */

(function() {
    'use strict';
    
    // Get API base URL dynamically
    function getApiBase() {
        const path = window.location.pathname;
        const publicIndex = path.indexOf('/public/');
        if (publicIndex !== -1) {
            return window.location.origin + path.substring(0, publicIndex + 7) + '/api';
        }
        // Fallback
        return window.location.origin + '/QuickShop/public/api';
    }
    
    // Export API_BASE to window
    window.API_BASE = getApiBase();
    window.getApiBase = getApiBase;
})();

