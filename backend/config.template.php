<?php
/**
 * Database Configuration Template
 * 
 * IMPORTANT SECURITY NOTE:
 * For production use, DO NOT commit database credentials to version control.
 * 
 * Instead:
 * 1. Copy this file to config.php (already done)
 * 2. Update the credentials in config.php with your actual database details
 * 3. Add config.php to .gitignore if you want to keep it private
 * 4. Or use environment variables (recommended for production)
 */

// Database Configuration
// CHANGE THESE VALUES FOR YOUR SETUP
define('DB_HOST', 'your_mysql_host');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'your_database_name');
define('DB_PORT', '3306');

// CORS Headers - restrict to your frontend domain in production
// Add your GitHub Pages URL here
$allowedOrigins = [
    'https://yourusername.github.io',
    'http://localhost',        // For local development
    'https://localhost'         // For local development with HTTPS
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // For development only - REMOVE THIS IN PRODUCTION
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
// CHANGE THIS TO A RANDOM STRING IN PRODUCTION
// Generate with: openssl rand -base64 32
define('JWT_SECRET', 'change_this_to_a_random_secret_key_in_production');

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
        echo json_encode(['error' => 'Database connection failed']);
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
