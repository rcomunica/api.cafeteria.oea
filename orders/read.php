<?php
require_once __DIR__ . '/../config/auth.php';
requireAuth();

$pdo = connection();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if (isset($_GET['user_id']) && (!$userId || $userId < 1)) {
    response(['error' => 'user_id debe ser un entero válido'], 400);
}

$attachOrderItems = static function (array $orders) use ($pdo): array {
    if (!$orders) {
        return $orders;
    }

    $orderIds = array_column($orders, 'id');
    $placeholders = implode(', ', array_fill(0, count($orderIds), '?'));
    $items = $pdo->prepare(
        "SELECT oi.id, oi.order_id, oi.product_id, p.name AS product_name,
                oi.unit_price AS price, oi.quantity, oi.subtotal
         FROM order_items oi
         INNER JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id IN ($placeholders)
         ORDER BY oi.order_id ASC, oi.id ASC"
    );
    $items->execute($orderIds);

    $itemsByOrder = [];
    foreach ($items->fetchAll() as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }

    foreach ($orders as &$order) {
        $order['order_items'] = $itemsByOrder[$order['id']] ?? [];
    }
    unset($order);

    return $orders;
};

if ($id) {
    $sql = 'SELECT * FROM orders WHERE id = ?';
    $params = [$id];
    if ($userId) {
        $sql .= ' AND user_id = ?';
        $params[] = $userId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch();

    if ($order) {
        $order = $attachOrderItems([$order])[0];
    }

    response($order ?: ['error' => 'Orden no encontrada'], $order ? 200 : 404);
}

if ($userId) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
    $stmt->execute([$userId]);
    response(['data' => $attachOrderItems($stmt->fetchAll())]);
}

$stmt = $pdo->query('SELECT * FROM orders ORDER BY id DESC');
response(['data' => $attachOrderItems($stmt->fetchAll())]);
