<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
updateRecord('payment_notifications', ['processed', 'processed_at']);
