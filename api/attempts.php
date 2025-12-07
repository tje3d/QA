<?php
/**
 * Attempts API
 * GET - List attempts for a category (current session)
 * POST - Create new attempt
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$sessionId = getUserSession($pdo);

switch ($method) {
    case 'GET':
        $categoryId = $_GET['category_id'] ?? null;
        
        if (!$categoryId) {
            jsonResponse(['success' => false, 'message' => 'شناسه دسته‌بندی الزامی است'], 400);
        }

        // Get all attempts for this category and session
        $stmt = $pdo->prepare('
            SELECT a.*, 
                   (SELECT COUNT(*) FROM answers ans WHERE ans.attempt_id = a.id) as answered_count,
                   (SELECT COUNT(*) FROM questions q WHERE q.category_id = a.category_id) as total_questions
            FROM attempts a 
            WHERE a.session_id = ? AND a.category_id = ?
            ORDER BY a.created_at DESC
        ');
        $stmt->execute([$sessionId, (int)$categoryId]);
        $attempts = $stmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $attempts]);
        break;

    case 'POST':
        $input = getJsonInput();
        $categoryId = $input['category_id'] ?? null;
        
        if (!$categoryId) {
            jsonResponse(['success' => false, 'message' => 'شناسه دسته‌بندی الزامی است'], 400);
        }

        // Create new attempt
        $stmt = $pdo->prepare('INSERT INTO attempts (session_id, category_id) VALUES (?, ?)');
        $stmt->execute([$sessionId, (int)$categoryId]);
        $attemptId = $pdo->lastInsertId();

        jsonResponse([
            'success' => true,
            'message' => 'تلاش جدید ایجاد شد',
            'attempt_id' => $attemptId
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}
