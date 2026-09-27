<?php
require_once __DIR__ . '/../config/auth.php';
requireAuth();
listRecords('products', []);
