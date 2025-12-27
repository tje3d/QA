<?php
/**
 * Admin Answers API
 * GET - Get all answers for a category
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

setCorsHeaders();
requireAdmin();

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}

$categoryId = $_GET['category_id'] ?? null;

if (!$categoryId) {
    jsonResponse(['success' => false, 'message' => 'شناسه دسته‌بندی الزامی است'], 400);
}

// Get questions for this category
$stmt = $pdo->prepare('SELECT * FROM questions WHERE category_id = ? ORDER BY sort_order ASC, question_group ASC, id ASC');
$stmt->execute([(int)$categoryId]);
$questions = $stmt->fetchAll();

// Get all attempts for this category
$stmt = $pdo->prepare('
    SELECT a.* 
    FROM attempts a 
    WHERE a.category_id = ? 
    ORDER BY a.created_at DESC
');
$stmt->execute([(int)$categoryId]);
$attempts = $stmt->fetchAll();

// Get all answers for these attempts
$answers = [];
if (!empty($attempts)) {
    $attemptIds = array_column($attempts, 'id');
    $placeholders = implode(',', array_fill(0, count($attemptIds), '?'));
    
    $stmt = $pdo->prepare("SELECT attempt_id, question_id, answer_value FROM answers WHERE attempt_id IN ($placeholders)");
    $stmt->execute($attemptIds);
    
    foreach ($stmt->fetchAll() as $row) {
        $key = $row['attempt_id'] . '_' . $row['question_id'];
        $answers[$key] = $row['answer_value'];
    }
}

jsonResponse([
    'success' => true,
    'questions' => $questions,
    'attempts' => $attempts,
    'answers' => $answers
]);
