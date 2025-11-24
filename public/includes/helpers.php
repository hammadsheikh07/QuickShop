<?php

/**
 * Helper functions for product display
 */

/**
 * Generate product initials for placeholder
 * Takes first letter of each word in product name
 */
function getProductInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    
    // Get first letter of first 2-3 words (max 3 characters)
    $maxInitials = min(3, count($words));
    for ($i = 0; $i < $maxInitials; $i++) {
        if (!empty($words[$i])) {
            $initials .= strtoupper(substr($words[$i], 0, 1));
        }
    }
    
    // If no words, use first 2-3 characters of the name
    if (empty($initials)) {
        $initials = strtoupper(substr($name, 0, 3));
    }
    
    return $initials;
}

/**
 * Generate a color based on product name (consistent for same product)
 * Returns a subtle, professional color
 */
function getProductColor($name) {
    // Generate a consistent color based on product name hash
    $hash = md5($name);
    $hue = hexdec(substr($hash, 0, 2)) % 360;
    
    // Use subtle, professional colors (low saturation, medium lightness)
    return "hsl({$hue}, 15%, 85%)";
}

function getStockClass($stock) {
    if ($stock === 0) return 'stock-out';
    if ($stock < 10) return 'stock-low';
    return 'stock-in';
}

function getStockText($stock) {
    if ($stock === 0) return 'Out of Stock';
    if ($stock < 10) return "Only {$stock} left";
    return "{$stock} in stock";
}

function formatPrice($price) {
    return number_format($price, 2, '.', '');
}

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

