<?php
// Database Configuration
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_39929369');
define('DB_PASS', 'hfy23whc');
define('DB_NAME', 'if0_39929369_blog');
define('DB_PORT', '3306');

// CORS Headers - restrict to GitHub Pages in production
// For production, change * to your GitHub Pages URL
$allowedOrigins = [
    'https://kychugo.github.io',
    'http://localhost',
    'https://localhost'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Allow all origins during development - CHANGE THIS IN PRODUCTION
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// JWT Secret Key
define('JWT_SECRET', 'hugo_blog_secret_key_2024_change_this_in_production');

// Database Connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}

// Simple JWT-like token generation
function generateToken($userId, $email, $isAdmin) {
    $payload = [
        'user_id' => $userId,
        'email' => $email,
        'is_admin' => $isAdmin,
        'exp' => time() + (86400 * 30) // 30 days
    ];
    return base64_encode(json_encode($payload) . '.' . hash_hmac('sha256', json_encode($payload), JWT_SECRET));
}

// Simple JWT-like token verification
function verifyToken($token) {
    try {
        $parts = explode('.', base64_decode($token));
        if (count($parts) !== 2) return false;
        
        $payload = json_decode($parts[0], true);
        $signature = $parts[1];
        
        if (hash_hmac('sha256', $parts[0], JWT_SECRET) !== $signature) {
            return false;
        }
        
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    } catch (Exception $e) {
        return false;
    }
}

// Get current user from Authorization header
function getCurrentUser() {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 
                 (isset($headers['authorization']) ? $headers['authorization'] : '');
    
    if (empty($authHeader)) {
        return null;
    }
    
    $token = str_replace('Bearer ', '', $authHeader);
    return verifyToken($token);
}

// Check if user is admin
function requireAdmin() {
    $user = getCurrentUser();
    if (!$user || !$user['is_admin']) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit();
    }
    return $user;
}

// Response helpers
function sendSuccess($data = [], $message = 'Success') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
}
?>
