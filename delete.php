<?php
// delete.php
require_once 'config.php';
$pdo = pdo();
$user = current_user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
if (!check_csrf($_POST['csrf'] ?? '')) die('CSRF');
$id = $_POST['id'] ?? '';
$stmt = $pdo->prepare("DELETE FROM posts WHERE id=:id AND author_id=:uid");
$stmt->execute(['id' => $id, 'uid' => $user['id']]);
header('Location: ?page=dashboard');
