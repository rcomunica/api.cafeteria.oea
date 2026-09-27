<?php

declare(strict_types=1);

require_once __DIR__ . '/crud.php';

function bearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(\S+)$/i', $header, $matches) !== 1) {
        return null;
    }
    return $matches[1];
}

function requireAuth(): array
{
    $token = bearerToken();
    if (!$token) {
        response(['error' => 'Token de autenticación requerido'], 401);
    }

    $stmt = connection()->prepare(
        'SELECT u.id, u.name, u.email, u.phone, u.role, u.status, t.id AS token_id
         FROM auth_tokens t
         INNER JOIN users u ON u.id = t.user_id
         WHERE t.token_hash = ? AND t.revoked_at IS NULL AND t.expires_at > NOW() AND u.status = \'active\''
    );
    $stmt->execute([hash('sha256', $token)]);
    $user = $stmt->fetch();

    if (!$user) {
        response(['error' => 'Token inválido o expirado'], 401);
    }
    return $user;
}

function requireRole(array $roles): array
{
    $user = requireAuth();
    if (!in_array($user['role'], $roles, true)) {
        response(['error' => 'No tiene permisos para realizar esta acción'], 403);
    }
    return $user;
}
