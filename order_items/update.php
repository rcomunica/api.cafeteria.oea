<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
updateRecord('order_items', ['quantity', 'unit_price']);
