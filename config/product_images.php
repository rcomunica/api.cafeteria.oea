<?php

declare(strict_types=1);

const PRODUCT_IMAGE_MAX_SIZE = 5242880;

function uploadProductImage(): ?string
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        response(['error' => 'No se pudo recibir la imagen'], 422);
    }
    if ($file['size'] > PRODUCT_IMAGE_MAX_SIZE) {
        response(['error' => 'La imagen no puede superar los 5 MB'], 422);
    }

    $imageInfo = getimagesize($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = $imageInfo['mime'] ?? '';
    if ($imageInfo === false || !isset($extensions[$mime])) {
        response(['error' => 'La imagen debe ser JPG, PNG, WEBP o GIF'], 422);
    }

    $directory = __DIR__ . '/../uploads/products';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        response(['error' => 'No se pudo preparar el almacenamiento de imágenes'], 500);
    }

    try {
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    } catch (Throwable $exception) {
        response(['error' => 'No se pudo generar el nombre de la imagen'], 500);
    }

    $path = $directory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        response(['error' => 'No se pudo guardar la imagen'], 500);
    }

    return '/uploads/products/' . $filename;
}

function deleteProductImage(?string $imageUrl): void
{
    if (!$imageUrl || !str_starts_with($imageUrl, '/uploads/products/')) {
        return;
    }

    $filename = basename($imageUrl);
    $path = __DIR__ . '/../uploads/products/' . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}
