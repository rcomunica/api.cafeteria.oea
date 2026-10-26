<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/product_images.php';
requireRole(['employee', 'admin']);

$data = cleanData(input(), ['name', 'description', 'category', 'price', 'qty', 'image_url', 'is_available']);
required($data, ['name', 'category', 'price', 'qty']);

$imageUrl = uploadProductImage();
if ($imageUrl !== null) {
    $data['image_url'] = $imageUrl;
}
if (!$data) {
    response(['error' => 'No hay datos para crear'], 422);
}

$columns = array_keys($data);
$stmt = connection()->prepare(
    'INSERT INTO products (' . implode(',', $columns) . ') VALUES (' .
    implode(',', array_fill(0, count($columns), '?')) . ')'
);
$stmt->execute(array_values($data));
response(['id' => (int) connection()->lastInsertId(), 'message' => 'Registro creado'], 201);
