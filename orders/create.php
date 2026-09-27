<?php
require_once __DIR__ . '/../config/auth.php';
$user = requireAuth();
$data = input();
$data['user_id'] = $user['id'];
$data['pickup_code'] = strtoupper(bin2hex(random_bytes(4)));
$data['total'] = $data['total'] ?? 0;
$fields = ['user_id', 'status', 'total', 'pickup_code', 'notes'];
$data = cleanData($data, $fields);
$columns = array_keys($data);
$stmt = connection()->prepare("INSERT INTO orders (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")");
$stmt->execute(array_values($data));
response(['id' => (int) connection()->lastInsertId(), 'pickup_code' => $data['pickup_code'], 'message' => 'Orden creada'], 201);
