<?php
/**
 * Verify Category Password API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}

$pdo = Database::getInstance()->getConnection();
$input = getJsonInput();

$categoryId = $input['category_id'] ?? null;
$password = $input['password'] ?? '';

if (!$categoryId) {
    jsonResponse(['success' => false, 'message' => 'شناسه دسته‌بندی الزامی است'], 400);
}

$stmt = $pdo->prepare('SELECT password FROM categories WHERE id = ?');
$stmt->execute([(int)$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    jsonResponse(['success' => false, 'message' => 'دسته‌بندی یافت نشد'], 404);
}

// If no password set, it's always verified
if (empty($category['password'])) {
    jsonResponse(['success' => true]);
}

if (password_verify($password, $category['password'])) {
    // Store in session that this category is unlocked for this user
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['unlocked_categories'][$categoryId] = true;
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['success' => false, 'message' => 'رمز عبور اشتباه است']);
}
