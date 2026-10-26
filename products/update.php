<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/product_images.php';
requireRole(['employee', 'admin']);

$id = idFromRequest();
$data = cleanData(input(), ['name', 'description', 'category', 'price', 'qty', 'image_url', 'is_available']);
$imageUrl = uploadProductImage();
if ($imageUrl !== null) {
    $data['image_url'] = $imageUrl;
}
if (!$data) {
    response(['error' => 'No hay datos para actualizar'], 422);
}

$assignments = implode(', ', array_map(static fn($field) => "{$field} = ?", array_keys($data)));
$values = array_values($data);
$values[] = $id;
$stmt = connection()->prepare("UPDATE products SET {$assignments} WHERE id = ?");
$stmt->execute($values);
if ($stmt->rowCount()) {
    response(['message' => 'Registro actualizado']);
}

if ($imageUrl !== null) {
    deleteProductImage($imageUrl);
}
response(['message' => 'Registro no encontrado'], 404);
