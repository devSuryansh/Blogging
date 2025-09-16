<?php
// like.php
require_once 'config.php';
$pdo = pdo();
$user = current_user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?page=login');
    exit;
}
if (!check_csrf($_POST['csrf'] ?? '')) {
    die('CSRF');
}
$post_id = $_POST['post_id'] ?? '';
try {
    // toggle like: try insert, if duplicate then delete
    $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (:pid, :uid)");
    $stmt->execute(['pid' => $post_id, 'uid' => $user['id']]);
} catch (PDOException $e) {
    // delete
    $d = $pdo->prepare("DELETE FROM likes WHERE post_id=:pid AND user_id=:uid");
    $d->execute(['pid' => $post_id, 'uid' => $user['id']]);
}
header('Location: ?page=view&id=' . urlencode($post_id));
