<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['employee', 'admin']);
createRecord('products', ['name', 'description', 'category', 'price', 'qty', 'image_url', 'is_available'], ['name', 'category', 'price', 'qty']);
