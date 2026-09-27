<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['admin']);
$pdo = connection();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = $pdo->prepare('SELECT id, name, email, phone, role, status, created_at, updated_at FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    response($user ?: ['error' => 'Usuario no encontrado'], $user ? 200 : 404);
}
$stmt = $pdo->query('SELECT id, name, email, phone, role, status, created_at, updated_at FROM users ORDER BY id DESC');
response($stmt->fetchAll());
