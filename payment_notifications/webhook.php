<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/crud.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'El webhook solo acepta solicitudes POST'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    response(['error' => 'El cuerpo debe ser un JSON válido'], 400);
}

$transaction = $payload['data']['transaction'] ?? null;
if (!is_array($transaction) || !isset($transaction['reference'], $transaction['status'])) {
    response(['error' => 'La notificación no contiene una transacción válida'], 422);
}

$reference = trim((string) $transaction['reference']);
$wompiStatus = strtoupper(trim((string) $transaction['status']));
$paymentStatus = match ($wompiStatus) {
    'APPROVED' => 'approved',
    'DECLINED', 'VOIDED', 'ERROR' => 'rejected',
    default => 'pending',
};
$orderStatus = match ($wompiStatus) {
    'APPROVED' => 'paid',
    'DECLINED', 'VOIDED', 'ERROR' => 'cancelled',
    default => null,
};

$pdo = connection();
$pdo->beginTransaction();

try {
    $paymentStatement = $pdo->prepare(
        'SELECT id, order_id FROM payments WHERE transaction_reference = ? FOR UPDATE'
    );
    $paymentStatement->execute([$reference]);
    $payment = $paymentStatement->fetch();

    if (!$payment) {
        $pdo->rollBack();
        response(['error' => 'No existe un pago con esa referencia'], 404);
    }

    $notificationStatement = $pdo->prepare(
        'INSERT INTO payment_notifications
            (payment_id, provider, event_type, external_reference, payload, processed, processed_at)
         VALUES (?, \'wompi\', ?, ?, ?, TRUE, NOW())'
    );
    $notificationStatement->execute([
        $payment['id'],
        (string) ($payload['event'] ?? 'transaction.updated'),
        $reference,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    ]);

    $paymentUpdate = $pdo->prepare(
        'UPDATE payments
         SET status = ?, paid_at = CASE WHEN ? = \'approved\' THEN COALESCE(paid_at, NOW()) ELSE paid_at END
         WHERE id = ?'
    );
    $paymentUpdate->execute([$paymentStatus, $paymentStatus, $payment['id']]);

    if ($orderStatus !== null) {
        $orderUpdate = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $orderUpdate->execute([$orderStatus, $payment['order_id']]);
    }

    $pdo->commit();
    response([
        'message' => 'Notificación procesada',
        'reference' => $reference,
        'status' => $wompiStatus,
    ]);
} catch (JsonException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    response(['error' => 'No fue posible serializar la notificación'], 422);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    response(['error' => 'No fue posible procesar la notificación'], 500);
}
