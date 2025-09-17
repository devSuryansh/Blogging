<?php
// comment.php
require_once 'config.php';
$pdo = pdo();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!$user) {
    header('Location: ?page=login');
    exit;
}

if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');

$post_id = $_POST['post_id'] ?? '';
$content = trim($_POST['content'] ?? '');

if ($content === '') {
    header('Location: ?page=view&id=' . urlencode($post_id));
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (post_id, author_name, author_email, content) VALUES (:pid,:name,:email,:content)");
$stmt->execute([
    'pid' => $post_id,
    'name' => $user['display_name'] ?? $user['email'],
    'email' => $user['email'],
    'content' => $content
]);

header('Location: ?page=view&id=' . urlencode($post_id) . '#comments');
