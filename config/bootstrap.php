<?php
// config/bootstrap.php
// Enhanced configuration system for production deployment

// Load environment variables from .env file if it exists
function loadEnv($file = '.env')
{
    if (!file_exists($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes
        $value = trim($value, '"\'');

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

// Get environment variable with default
function env($key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Load environment
loadEnv(__DIR__ . '/../.env');

// Application Configuration
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
define('APP_URL', env('APP_URL', 'http://localhost:8000'));
define('APP_SECRET', env('APP_SECRET', 'change-this-secret-key'));

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '5432'));
define('DB_NAME', env('DB_NAME', 'blogging'));
define('DB_USER', env('DB_USER', 'admin'));
define('DB_PASS', env('DB_PASS', 'qwerty'));

// Security Configuration
define('SESSION_DOMAIN', env('SESSION_DOMAIN', ''));
define('SESSION_SECURE', filter_var(env('SESSION_SECURE', APP_ENV === 'production'), FILTER_VALIDATE_BOOLEAN));
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 3600));

// File Upload Configuration
define('UPLOAD_MAX_SIZE', (int)env('UPLOAD_MAX_SIZE', 5242880));
define('UPLOAD_PATH', env('UPLOAD_PATH', __DIR__ . '/../uploads'));

// Logging Configuration
define('LOG_LEVEL', env('LOG_LEVEL', 'INFO'));
define('LOG_PATH', env('LOG_PATH', __DIR__ . '/../logs'));

// Cache Configuration
define('CACHE_ENABLED', filter_var(env('CACHE_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN));
define('CACHE_TTL', (int)env('CACHE_TTL', 3600));

// Rate Limiting
define('RATE_LIMIT', (int)env('RATE_LIMIT', 60));

// Maintenance Mode
define('MAINTENANCE_MODE', filter_var(env('MAINTENANCE_MODE', 'false'), FILTER_VALIDATE_BOOLEAN));

// Enhanced PDO function with connection pooling and error handling
function pdo()
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);

    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => APP_ENV === 'production',
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Set timezone
        $pdo->exec("SET TIME ZONE 'UTC'");
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());

        if (APP_DEBUG) {
            die("Database connection failed: " . htmlspecialchars($e->getMessage()));
        } else {
            die("Database connection failed. Please try again later.");
        }
    }

    return $pdo;
}

// Enhanced session configuration
function initializeSession()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');

    if (SESSION_SECURE) {
        ini_set('session.cookie_secure', 1);
    }

    if (SESSION_DOMAIN) {
        ini_set('session.cookie_domain', SESSION_DOMAIN);
    }

    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);

    // Regenerate session ID periodically
    session_start();

    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// CSRF token management
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf($token)
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

// User authentication
function current_user()
{
    if (!empty($_SESSION['user_id'])) {
        try {
            $stmt = pdo()->prepare("SELECT id, email, display_name FROM users WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching current user: " . $e->getMessage());
            return null;
        }
    }
    return null;
}

// Logging function
function writeLog($level, $message, $context = [])
{
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }

    $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3];
    $currentLevel = $levels[LOG_LEVEL] ?? 1;

    if ($levels[$level] >= $currentLevel) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        file_put_contents(LOG_PATH . '/app.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Rate limiting
function checkRateLimit($identifier = null)
{
    if (!$identifier) {
        $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    $key = 'rate_limit_' . md5($identifier);
    $file = sys_get_temp_dir() . '/' . $key;

    $now = time();
    $requests = [];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $requests = array_filter($data['requests'] ?? [], function ($time) use ($now) {
            return $now - $time < 60; // Last minute
        });
    }

    if (count($requests) >= RATE_LIMIT) {
        return false;
    }

    $requests[] = $now;
    file_put_contents($file, json_encode(['requests' => $requests]));

    return true;
}

// Maintenance mode check
function checkMaintenanceMode()
{
    if (MAINTENANCE_MODE) {
        http_response_code(503);
        include __DIR__ . '/../maintenance.html';
        exit;
    }
}

// Initialize everything
initializeSession();
checkMaintenanceMode();
