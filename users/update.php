<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['admin']);
$data = input();
if (isset($data['password'])) {
    $data['password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    unset($data['password']);
}
$fields = ['name', 'email', 'phone', 'password_hash', 'role', 'status'];
updateRecord('users', $fields);
