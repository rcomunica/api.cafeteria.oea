<?php
require_once __DIR__ . '/../config/auth.php';
requireAuth();
createRecord('payments', ['order_id', 'provider', 'transaction_reference', 'amount', 'status', 'paid_at'], ['order_id', 'amount']);
