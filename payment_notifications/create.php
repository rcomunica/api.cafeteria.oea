<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
$data = input();
required($data, ['event_type', 'payload']);
$fields = ['payment_id', 'provider', 'event_type', 'external_reference', 'payload'];
$data = cleanData($data, $fields);
$data['payload'] = is_string($data['payload']) ? $data['payload'] : json_encode($data['payload'], JSON_UNESCAPED_UNICODE);
$pdo = connection();
$pdo->beginTransaction();
try {
    $columns = array_keys($data);
    $stmt = $pdo->prepare("INSERT INTO payment_notifications (" . implode(',', $columns) . ") VALUES (" . implode(',', array_fill(0, count($columns), '?')) . ")");
    $stmt->execute(array_values($data));
    $notificationId = (int) $pdo->lastInsertId();
    if (!empty($data['payment_id']) && in_array(strtolower((string) $data['event_type']), ['approved', 'payment.approved', 'success'], true)) {
        $payment = $pdo->prepare('UPDATE payments SET status = \'approved\', paid_at = NOW() WHERE id = ?');
        $payment->execute([$data['payment_id']]);
        $order = $pdo->prepare("UPDATE orders o JOIN payments p ON p.order_id = o.id SET o.status = 'paid' WHERE p.id = ?");
        $order->execute([$data['payment_id']]);
        $pdo->prepare('UPDATE payment_notifications SET processed = TRUE, processed_at = NOW() WHERE id = ?')->execute([$notificationId]);
    }
    $pdo->commit();
    response(['id' => $notificationId, 'message' => 'Notificación recibida'], 201);
} catch (Throwable $exception) {
    $pdo->rollBack();
    response(['error' => 'No fue posible procesar la notificación'], 500);
}
