<?php

/**
 * Initialize session early, before any output
 * This should be included at the very beginning of PHP files
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

