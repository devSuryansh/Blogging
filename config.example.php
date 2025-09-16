<?php
// config.example.php
// Copy this file to config.php and update with your environment values

// Environment setting (development, staging, production)
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost:8000');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'blogging');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// Security Configuration
define('APP_SECRET', 'your-secret-key-here-change-this-in-production');
define('SESSION_DOMAIN', ''); // Set to your domain in production
define('SESSION_SECURE', false); // Set to true in production with HTTPS
define('SESSION_LIFETIME', 3600); // 1 hour

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', __DIR__ . '/uploads');

// Email Configuration (for future features)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('FROM_EMAIL', 'noreply@yourdomain.com');

// Logging Configuration
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_PATH', __DIR__ . '/logs');

// Cache Configuration
define('CACHE_ENABLED', false);
define('CACHE_TTL', 3600);

// Rate Limiting (requests per minute)
define('RATE_LIMIT', 60);

// Maintenance Mode
define('MAINTENANCE_MODE', false);
