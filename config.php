<?php
// config.php
// UPDATE these with your DB credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'blogging');
define('DB_USER', 'admin');
define('DB_PASS', 'qwerty');


function pdo()
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("DB connection failed: " . htmlspecialchars($e->getMessage()));
    }
    return $pdo;
}

// start session (use secure flags in prod)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// CSRF token helper
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_token()
{
    return $_SESSION['csrf_token'];
}
function check_csrf($token)
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

function current_user()
{
    if (!empty($_SESSION['user_id'])) {
        $stmt = pdo()->prepare("SELECT id, email, display_name FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}
