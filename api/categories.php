<?php
/**
 * Categories API
 * GET - List all categories
 * POST - Create new category
 * PUT - Update category
 * DELETE - Delete category
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

$pdo = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List all categories with question count
        $stmt = $pdo->query('
            SELECT c.*, COUNT(q.id) as question_count 
            FROM categories c 
            LEFT JOIN questions q ON c.id = q.category_id 
            GROUP BY c.id 
            ORDER BY c.created_at DESC
        ');
        $categories = $stmt->fetchAll();
        jsonResponse(['success' => true, 'data' => $categories]);
        break;

    case 'POST':
        requireAdmin();
        $input = getJsonInput();
        
        if (empty($input['title'])) {
            jsonResponse(['success' => false, 'message' => 'عنوان الزامی است'], 400);
        }

        $stmt = $pdo->prepare('INSERT INTO categories (title, description) VALUES (?, ?)');
        $stmt->execute([
            sanitize($input['title']),
            sanitize($input['description'] ?? '')
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'دسته‌بندی با موفقیت ایجاد شد',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        requireAdmin();
        $input = getJsonInput();
        
        if (empty($input['id']) || empty($input['title'])) {
            jsonResponse(['success' => false, 'message' => 'اطلاعات ناقص است'], 400);
        }

        $stmt = $pdo->prepare('UPDATE categories SET title = ?, description = ? WHERE id = ?');
        $stmt->execute([
            sanitize($input['title']),
            sanitize($input['description'] ?? ''),
            (int)$input['id']
        ]);

        jsonResponse(['success' => true, 'message' => 'دسته‌بندی با موفقیت به‌روزرسانی شد']);
        break;

    case 'DELETE':
        requireAdmin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'شناسه الزامی است'], 400);
        }

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int)$id]);

        jsonResponse(['success' => true, 'message' => 'دسته‌بندی با موفقیت حذف شد']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'متد پشتیبانی نمی‌شود'], 405);
}
