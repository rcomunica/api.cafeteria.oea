<?php
require_once __DIR__ . '/../config/crud.php';
$data = input();
required($data, ['name', 'email', 'password']);
$data['password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
unset($data['password']);
$data['role'] = 'student';
$data['status'] = 'active';
$fields = ['name', 'email', 'phone', 'password_hash', 'role', 'status'];
$data = cleanData($data, $fields);
$columns = array_keys($data);
$stmt = connection()->prepare("INSERT INTO users (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")");
$stmt->execute(array_values($data));
response(['id' => (int) connection()->lastInsertId(), 'message' => 'Usuario creado'], 201);
