<?php
// config.php
// Main configuration file - loads enhanced configuration system

// Load the bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

// Legacy function aliases for backward compatibility
// (These will use the enhanced versions from bootstrap.php)

// Backward compatibility for existing code
if (!function_exists('pdo')) {
    die('Configuration system not loaded properly');
}

if (!function_exists('csrf_token')) {
    die('Security system not loaded properly');
}

if (!function_exists('current_user')) {
    die('Authentication system not loaded properly');
}
