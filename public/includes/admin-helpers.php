<?php

/**
 * Admin helper functions
 */

use App\Config\Database;
use App\Repositories\AdminRepository;
use App\Services\AuthService;

/**
 * Get the authentication service instance
 */
function getAuthService(): AuthService
{
    static $authService = null;
    if ($authService === null) {
        $authService = new AuthService(new AdminRepository(Database::getConnection()));
    }
    return $authService;
}

/**
 * Check if user is authenticated as admin
 */
function isAdminAuthenticated(): bool
{
    return getAuthService()->isAuthenticated();
}

/**
 * Require admin authentication, redirect to login if not authenticated
 */
function requireAdminAuth(): void
{
    if (!isAdminAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current admin username
 */
function getCurrentAdminUsername(): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    return $_SESSION['admin_username'] ?? null;
}

