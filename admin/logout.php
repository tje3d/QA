<?php
require_once __DIR__ . '/../includes/functions.php';
adminLogout();
header('Location: /admin/login.php');
exit;
