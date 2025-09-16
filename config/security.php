<?php
// config/security.php
// Enhanced security functions and middleware

// Security headers for production
function setSecurityHeaders()
{
    if (APP_ENV === 'production') {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // Content Security Policy
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self';";
        header("Content-Security-Policy: {$csp}");
    }
}

// Input sanitization
function sanitizeInput($input, $type = 'string')
{
    if (is_array($input)) {
        return array_map(function ($item) use ($type) {
            return sanitizeInput($item, $type);
        }, $input);
    }

    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'html':
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Password strength validation
function validatePasswordStrength($password)
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }

    return $errors;
}

// Enhanced password hashing
function hashPassword($password)
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3,         // 3 threads
    ]);
}

// File upload security
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'])
{
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed with error code: " . $file['error'];
        return $errors;
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = "File size exceeds maximum allowed size";
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = "File type not allowed";
    }

    // Additional security: Check file content
    if (strpos($mimeType, 'image/') === 0) {
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = "Invalid image file";
        }
    }

    return $errors;
}

// SQL injection prevention (additional layer)
function sanitizeForSQL($input)
{
    // This is an additional layer - we still use prepared statements
    return preg_replace('/[^a-zA-Z0-9\-_\s]/', '', $input);
}

// XSS prevention for rich text content
function sanitizeRichText($html)
{
    // Allow safe HTML tags for blog content
    $allowedTags = '<p><br><strong><em><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img>';

    // Strip dangerous tags
    $html = strip_tags($html, $allowedTags);

    // Remove dangerous attributes
    $html = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|data:)[^>]*>/i', '$1>', $html);

    return $html;
}

// Audit logging for security events
function logSecurityEvent($event, $details = [])
{
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'event' => $event,
        'details' => $details
    ];

    writeLog('WARNING', "Security Event: {$event}", $entry);
}

// Brute force protection
function checkBruteForce($identifier, $maxAttempts = 5, $timeWindow = 900)
{ // 15 minutes
    $key = 'brute_force_' . md5($identifier);
    $file = sys_get_temp_dir() . '/' . $key;

    $now = time();
    $attempts = [];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $attempts = array_filter($data['attempts'] ?? [], function ($time) use ($now, $timeWindow) {
            return $now - $time < $timeWindow;
        });
    }

    if (count($attempts) >= $maxAttempts) {
        logSecurityEvent('brute_force_blocked', ['identifier' => $identifier]);
        return false;
    }

    return true;
}

function recordFailedAttempt($identifier)
{
    $key = 'brute_force_' . md5($identifier);
    $file = sys_get_temp_dir() . '/' . $key;

    $now = time();
    $attempts = [];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $attempts = array_filter($data['attempts'] ?? [], function ($time) use ($now) {
            return $now - $time < 900; // Keep last 15 minutes
        });
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode(['attempts' => $attempts]));
}

// Initialize security headers
setSecurityHeaders();
