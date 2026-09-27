<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
deleteRecord('order_items');
