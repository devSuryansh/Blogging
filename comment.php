<?php
// comment.php
require_once 'config.php';
$pdo = pdo();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');
$post_id = $_POST['post_id'] ?? '';
$author = trim($_POST['author_name'] ?? '');
$email = trim($_POST['author_email'] ?? '');
$content = trim($_POST['content'] ?? '');
if ($author === '' || $content === '') {
    header('Location: ?page=view&id=' . urlencode($post_id));
    exit;
}
$stmt = $pdo->prepare("INSERT INTO comments (post_id, author_name, author_email, content) VALUES (:pid,:name,:email,:content)");
$stmt->execute(['pid' => $post_id, 'name' => $author, 'email' => $email, 'content' => $content]);
header('Location: ?page=view&id=' . urlencode($post_id) . '#comments');
