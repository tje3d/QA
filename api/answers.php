<?php
/**
 * Answers API
 * GET - Get answers for an attempt
 * POST - Save/update a single answer (auto-save)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$sessionId = getUserSession($pdo);

switch ($method) {
    case 'GET':
        $attemptId = $_GET['attempt_id'] ?? null;
        
        if (!$attemptId) {
            jsonResponse(['success' => false, 'message' => 'شناسه تلاش الزامی است'], 400);
        }

        // Verify attempt belongs to current session
        $stmt = $pdo->prepare('SELECT id FROM attempts WHERE id = ? AND session_id = ?');
        $stmt->execute([(int)$attemptId, $sessionId]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'تلاش یافت نشد'], 404);
        }

        // Get all answers for this attempt
        $stmt = $pdo->prepare('SELECT question_id, answer_value FROM answers WHERE attempt_id = ?');
        $stmt->execute([(int)$attemptId]);
        $answers = $stmt->fetchAll();

        // Convert to key-value map
        $answerMap = [];
        foreach ($answers as $a) {
            $answerMap[$a['question_id']] = $a['answer_value'];
        }

        jsonResponse(['success' => true, 'data' => $answerMap]);
        break;

    case 'POST':
        $input = getJsonInput();
        
        if (empty($input['attempt_id']) || empty($input['question_id'])) {
            jsonResponse(['success' => false, 'message' => 'اطلاعات ناقص است'], 400);
        }

        $attemptId = (int)$input['attempt_id'];
        $questionId = (int)$input['question_id'];
        $answerValue = $input['answer_value'] ?? '';

        // Verify attempt belongs to current session
        $stmt = $pdo->prepare('SELECT id FROM attempts WHERE id = ? AND session_id = ?');
        $stmt->execute([$attemptId, $sessionId]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'تلاش یافت نشد'], 404);
        }

        // For multiselect, store as JSON
        if (is_array($answerValue)) {
            $answerValue = json_encode($answerValue, JSON_UNESCAPED_UNICODE);
        }

        // Upsert answer
        $stmt = $pdo->prepare('
            INSERT INTO answers (attempt_id, question_id, answer_value) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE answer_value = VALUES(answer_value), updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([$attemptId, $questionId, $answerValue]);

        jsonResponse(['success' => true, 'message' => 'پاسخ ذخیره شد']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}
