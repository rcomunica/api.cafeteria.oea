<?php
require_once __DIR__ . '/../config/auth.php';
requireAuth();
createRecord('order_items', ['order_id', 'product_id', 'quantity', 'unit_price'], ['order_id', 'product_id', 'quantity', 'unit_price']);
