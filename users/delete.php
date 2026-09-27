<?php
require_once __DIR__ . '/../config/auth.php';
requireRole(['admin']);
deleteRecord('users');
