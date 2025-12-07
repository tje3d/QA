<?php
require_once __DIR__ . '/../includes/functions.php';
adminLogout();
header('Location: login.php');
exit;
