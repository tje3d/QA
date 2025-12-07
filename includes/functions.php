<?php
/**
 * Helper Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

/**
 * Get or create user session
 */
function getUserSession($pdo) {
    if (!isset($_COOKIE['user_session'])) {
        $token = bin2hex(random_bytes(32));
        setcookie('user_session', $token, time() + (86400 * 365), '/'); // 1 year
        $_COOKIE['user_session'] = $token;
    }

    $token = $_COOKIE['user_session'];

    // Check if session exists
    $stmt = $pdo->prepare('SELECT id FROM user_sessions WHERE session_token = ?');
    $stmt->execute([$token]);
    $session = $stmt->fetch();

    if ($session) {
        return $session['id'];
    }

    // Create new session
    $stmt = $pdo->prepare('INSERT INTO user_sessions (session_token) VALUES (?)');
    $stmt->execute([$token]);
    return $pdo->lastInsertId();
}

/**
 * Admin Authentication
 */
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        // Handle API requests
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Handle Admin pages (assumed to be in /admin/)
        header('Location: login.php');
        exit;
    }
}

function adminLogin($username, $password) {
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function adminLogout() {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * CORS headers for API
 */
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
}
