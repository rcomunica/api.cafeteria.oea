<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
updateRecord('payments', ['transaction_reference', 'status', 'paid_at']);
