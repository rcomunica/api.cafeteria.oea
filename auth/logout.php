<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

$user = requireAuth();
$token = bearerToken();
$stmt = connection()->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE id = ?');
$stmt->execute([$user['token_id']]);
response(['message' => 'Sesión cerrada']);
