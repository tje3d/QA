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
                ORDER BY sort_order ASC, question_group ASC, id ASC
            ');
            $stmt->execute([(int)$categoryId]);
        } else {
            $stmt = $pdo->query('
                SELECT q.*, c.title as category_title 
                FROM questions q 
                JOIN categories c ON q.category_id = c.id 
                ORDER BY q.category_id, q.sort_order ASC, q.question_group ASC, q.id ASC
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

        $validTypes = ['boolean', 'text', 'textarea', 'select', 'multiselect', 'city_province', 'dropdown'];
        if (!in_array($input['answer_type'], $validTypes)) {
            jsonResponse(['success' => false, 'message' => 'نوع پاسخ نامعتبر است'], 400);
        }

        $options = null;
        if (in_array($input['answer_type'], ['select', 'multiselect', 'dropdown']) && !empty($input['options'])) {
            $options = json_encode($input['options'], JSON_UNESCAPED_UNICODE);
        }

        $sortOrder = (int)($input['sort_order'] ?? 0);
        $categoryId = (int)$input['category_id'];

        // Get count of questions in this category to limit sort_order
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        $count = (int)$stmt->fetchColumn();
        
        // Clamp sortOrder between 1 and count + 1
        if ($sortOrder < 1) $sortOrder = 1;
        if ($sortOrder > $count + 1) $sortOrder = $count + 1;

        // Shift existing questions' sort_order
        $stmt = $pdo->prepare('
            UPDATE questions 
            SET sort_order = sort_order + 1 
            WHERE category_id = ? AND sort_order >= ?
        ');
        $stmt->execute([$categoryId, $sortOrder]);

        $stmt = $pdo->prepare('
            INSERT INTO questions (category_id, question_text, answer_type, options, placeholder, question_group, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $categoryId,
            sanitize($input['question_text']),
            $input['answer_type'],
            $options,
            sanitize($input['placeholder'] ?? ''),
            sanitize($input['question_group'] ?? 'عمومی'),
            $sortOrder
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
        if (in_array($input['answer_type'], ['select', 'multiselect', 'dropdown']) && !empty($input['options'])) {
            $options = json_encode($input['options'], JSON_UNESCAPED_UNICODE);
        }

        $id = (int)$input['id'];
        $newSortOrder = (int)($input['sort_order'] ?? 0);
        $newCategoryId = (int)$input['category_id'];

        // Get count of questions in the target category to limit sort_order
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE category_id = ?');
        $stmt->execute([$newCategoryId]);
        $count = (int)$stmt->fetchColumn();

        // Get current question info to see if sort_order or category changed
        $stmt = $pdo->prepare('SELECT category_id, sort_order FROM questions WHERE id = ?');
        $stmt->execute([$id]);
        $current = $stmt->fetch();

        if ($current) {
            $oldSortOrder = (int)$current['sort_order'];
            $oldCategoryId = (int)$current['category_id'];

            // Adjust count if it's the same category (max is count, if different category max is count + 1)
            $maxOrder = ($newCategoryId === $oldCategoryId) ? $count : $count + 1;
            
            // Clamp newSortOrder between 1 and maxOrder
            if ($newSortOrder < 1) $newSortOrder = 1;
            if ($newSortOrder > $maxOrder) $newSortOrder = $maxOrder;

            if ($newCategoryId !== $oldCategoryId) {
                // Category changed: 
                // 1. Shift down items in OLD category to close the gap
                $stmt = $pdo->prepare('UPDATE questions SET sort_order = sort_order - 1 WHERE category_id = ? AND sort_order > ?');
                $stmt->execute([$oldCategoryId, $oldSortOrder]);

                // 2. Shift up items in NEW category to make room
                $stmt = $pdo->prepare('UPDATE questions SET sort_order = sort_order + 1 WHERE category_id = ? AND sort_order >= ?');
                $stmt->execute([$newCategoryId, $newSortOrder]);
            } elseif ($newSortOrder !== $oldSortOrder) {
                // Same category, different order
                if ($newSortOrder > $oldSortOrder) {
                    // Moving down: shift items between old and new orders UP (decrement sort_order)
                    $stmt = $pdo->prepare('
                        UPDATE questions 
                        SET sort_order = sort_order - 1 
                        WHERE category_id = ? AND sort_order > ? AND sort_order <= ? AND id != ?
                    ');
                    $stmt->execute([$newCategoryId, $oldSortOrder, $newSortOrder, $id]);
                } else {
                    // Moving up: shift items between new and old orders DOWN (increment sort_order)
                    $stmt = $pdo->prepare('
                        UPDATE questions 
                        SET sort_order = sort_order + 1 
                        WHERE category_id = ? AND sort_order >= ? AND sort_order < ? AND id != ?
                    ');
                    $stmt->execute([$newCategoryId, $newSortOrder, $oldSortOrder, $id]);
                }
            }
        }

        $stmt = $pdo->prepare('
            UPDATE questions 
            SET category_id = ?, question_text = ?, answer_type = ?, options = ?, placeholder = ?, question_group = ?, sort_order = ? 
            WHERE id = ?
        ');
        $stmt->execute([
            $newCategoryId,
            sanitize($input['question_text']),
            $input['answer_type'],
            $options,
            sanitize($input['placeholder'] ?? ''),
            sanitize($input['question_group'] ?? 'عمومی'),
            $newSortOrder,
            $id
        ]);

        jsonResponse(['success' => true, 'message' => 'سوال با موفقیت به‌روزرسانی شد']);
        break;

    case 'DELETE':
        requireAdmin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'شناسه الزامی است'], 400);
        }

        $id = (int)$id;

        // Get info before deleting to shift orders
        $stmt = $pdo->prepare('SELECT category_id, sort_order FROM questions WHERE id = ?');
        $stmt->execute([$id]);
        $current = $stmt->fetch();

        if ($current) {
            $categoryId = (int)$current['category_id'];
            $sortOrder = (int)$current['sort_order'];

            // Delete the question
            $stmt = $pdo->prepare('DELETE FROM questions WHERE id = ?');
            $stmt->execute([$id]);

            // Shift subsequent questions' sort_order down
            $stmt = $pdo->prepare('
                UPDATE questions 
                SET sort_order = sort_order - 1 
                WHERE category_id = ? AND sort_order > ?
            ');
            $stmt->execute([$categoryId, $sortOrder]);

            jsonResponse(['success' => true, 'message' => 'سوال با موفقیت حذف شد']);
        } else {
            jsonResponse(['success' => false, 'message' => 'سوال یافت نشد'], 404);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}
