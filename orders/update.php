<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
updateRecord('orders', ['status', 'total', 'notes']);
