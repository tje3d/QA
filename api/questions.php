<?php
/**
 * Questions API
 * GET - List questions by category
 * POST - Create new question
 * PUT - Update question
 * DELETE - Delete question
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $categoryId = $_GET['category_id'] ?? null;
        
        if ($categoryId) {
            $stmt = $pdo->prepare('
                SELECT * FROM questions 
                WHERE category_id = ? 
                ORDER BY question_group ASC, sort_order ASC, id ASC
            ');
            $stmt->execute([(int)$categoryId]);
        } else {
            $stmt = $pdo->query('
                SELECT q.*, c.title as category_title 
                FROM questions q 
                JOIN categories c ON q.category_id = c.id 
                ORDER BY q.category_id, q.question_group ASC, q.sort_order ASC, q.id ASC
            ');
        }
        
        $questions = $stmt->fetchAll();
        
        // Parse JSON options
        foreach ($questions as &$q) {
            $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
        }
        
        jsonResponse(['success' => true, 'data' => $questions]);
        break;

    case 'POST':
        requireAdmin();
        $input = getJsonInput();
        
        if (empty($input['category_id']) || empty($input['question_text']) || empty($input['answer_type'])) {
            jsonResponse(['success' => false, 'message' => 'اطلاعات ناقص است'], 400);
        }

        $validTypes = ['boolean', 'text', 'textarea', 'select', 'multiselect'];
        if (!in_array($input['answer_type'], $validTypes)) {
            jsonResponse(['success' => false, 'message' => 'نوع پاسخ نامعتبر است'], 400);
        }

        $options = null;
        if (in_array($input['answer_type'], ['select', 'multiselect']) && !empty($input['options'])) {
            $options = json_encode($input['options'], JSON_UNESCAPED_UNICODE);
        }

        $stmt = $pdo->prepare('
            INSERT INTO questions (category_id, question_text, answer_type, options, placeholder, question_group, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            (int)$input['category_id'],
            sanitize($input['question_text']),
            $input['answer_type'],
            $options,
            sanitize($input['placeholder'] ?? ''),
            sanitize($input['question_group'] ?? 'عمومی'),
            (int)($input['sort_order'] ?? 0)
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'سوال با موفقیت ایجاد شد',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        requireAdmin();
        $input = getJsonInput();
        
        if (empty($input['id']) || empty($input['question_text']) || empty($input['answer_type'])) {
            jsonResponse(['success' => false, 'message' => 'اطلاعات ناقص است'], 400);
        }

        $options = null;
        if (in_array($input['answer_type'], ['select', 'multiselect']) && !empty($input['options'])) {
            $options = json_encode($input['options'], JSON_UNESCAPED_UNICODE);
        }

        $stmt = $pdo->prepare('
            UPDATE questions 
            SET question_text = ?, answer_type = ?, options = ?, placeholder = ?, question_group = ?, sort_order = ? 
            WHERE id = ?
        ');
        $stmt->execute([
            sanitize($input['question_text']),
            $input['answer_type'],
            $options,
            sanitize($input['placeholder'] ?? ''),
            sanitize($input['question_group'] ?? 'عمومی'),
            (int)($input['sort_order'] ?? 0),
            (int)$input['id']
        ]);

        jsonResponse(['success' => true, 'message' => 'سوال با موفقیت به‌روزرسانی شد']);
        break;

    case 'DELETE':
        requireAdmin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'شناسه الزامی است'], 400);
        }

        $stmt = $pdo->prepare('DELETE FROM questions WHERE id = ?');
        $stmt->execute([(int)$id]);

        jsonResponse(['success' => true, 'message' => 'سوال با موفقیت حذف شد']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}
