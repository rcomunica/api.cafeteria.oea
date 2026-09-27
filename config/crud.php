<?php

declare(strict_types=1);

require_once __DIR__ . '/api.php';

function connection(): PDO
{
    static $pdo;
    return $pdo ??= require __DIR__ . '/database.php';
}

function cleanData(array $data, array $fields): array
{
    return array_intersect_key($data, array_flip($fields));
}

function listRecords(string $table, array $fields): void
{
    $pdo = connection();
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        response($record ?: ['error' => 'Registro no encontrado'], $record ? 200 : 404);
    }
    $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY id DESC");
    response(["data" => $stmt->fetchAll()]);
}

function createRecord(string $table, array $fields, array $required = []): void
{
    $data = cleanData(input(), $fields);
    required($data, $required);
    if (!$data) {
        response(['error' => 'No hay datos para crear'], 422);
    }
    $columns = array_keys($data);
    $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")";
    $stmt = connection()->prepare($sql);
    $stmt->execute(array_values($data));
    response(['id' => (int) connection()->lastInsertId(), 'message' => 'Registro creado'], 201);
}

function updateRecord(string $table, array $fields, array $required = []): void
{
    $id = idFromRequest();
    $data = cleanData(input(), $fields);
    required($data, $required);
    if (!$data) {
        response(['error' => 'No hay datos para actualizar'], 422);
    }
    $assignments = implode(', ', array_map(static fn($field) => "{$field} = ?", array_keys($data)));
    $stmt = connection()->prepare("UPDATE {$table} SET {$assignments} WHERE id = ?");
    $values = array_values($data);
    $values[] = $id;
    $stmt->execute($values);
    response(['message' => $stmt->rowCount() ? 'Registro actualizado' : 'Registro no encontrado'], $stmt->rowCount() ? 200 : 404);
}

function deleteRecord(string $table): void
{
    $id = idFromRequest();
    $stmt = connection()->prepare("DELETE FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    response(['message' => $stmt->rowCount() ? 'Registro eliminado' : 'Registro no encontrado'], $stmt->rowCount() ? 200 : 404);
}
