<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

$user = requireAuth();
unset($user['token_id']);
response(['user' => $user]);
