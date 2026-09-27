<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/crud.php';

$data = input();
required($data, ['email', 'password']);

$stmt = connection()->prepare('SELECT id, name, email, phone, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
$stmt->execute([strtolower(trim((string) $data['email']))]);
$user = $stmt->fetch();

if (!$user || $user['status'] !== 'active' || !password_verify((string) $data['password'], $user['password_hash'])) {
    response(['error' => 'Credenciales inválidas'], 401);
}

$token = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);
$expiresAt = (new DateTimeImmutable('+8 hours'))->format('Y-m-d H:i:s');
$insert = connection()->prepare('INSERT INTO auth_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
$insert->execute([$user['id'], $tokenHash, $expiresAt]);

unset($user['password_hash']);
response(['token' => $token, 'expires_at' => $expiresAt, 'user' => $user]);
