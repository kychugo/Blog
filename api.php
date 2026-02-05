<?php
/**
 * Hugo 開發日誌 API
 * Consolidated API endpoint handler
 * All-in-one API file for blog platform
 */

// Database Configuration
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_39929369');
define('DB_PASS', 'hfy23whc');
define('DB_NAME', 'if0_39929369_blog');
define('DB_PORT', '3306');
define('JWT_SECRET', 'hugo_blog_secret_key_2024_change_this_in_production');

// CORS Headers
$allowedOrigins = [
    'https://kychugo.github.io',
    'http://localhost',
    'https://localhost'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
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

// JWT-like token generation
function generateToken($userId, $email, $isAdmin) {
    $payload = [
        'user_id' => $userId,
        'email' => $email,
        'is_admin' => $isAdmin,
        'exp' => time() + (86400 * 30) // 30 days
    ];
    return base64_encode(json_encode($payload) . '.' . hash_hmac('sha256', json_encode($payload), JWT_SECRET));
}

// JWT-like token verification
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

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $path);
$path = trim($path, '/');

$conn = getDBConnection();

// ============= AUTHENTICATION ENDPOINTS =============

// Register endpoint
if ($method === 'POST' && strpos($path, 'auth/register') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password']) || !isset($input['username'])) {
        sendError('Email, password, and username are required');
        exit();
    }
    
    $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    $password = $input['password'];
    $username = htmlspecialchars($input['username']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Invalid email format');
        exit();
    }
    
    if (strlen($password) < 8) {
        sendError('Password must be at least 8 characters');
        exit();
    }
    
    try {
        // Check if this is the first user (will become admin)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        $isAdmin = ($result['count'] == 0);
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendError('Email already registered');
            exit();
        }
        
        // Create user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, username, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $username, $isAdmin]);
        
        $userId = $conn->lastInsertId();
        $token = generateToken($userId, $email, $isAdmin);
        
        sendSuccess([
            'token' => $token,
            'user' => [
                'id' => $userId,
                'email' => $email,
                'username' => $username,
                'is_admin' => $isAdmin
            ]
        ], $isAdmin ? 'Registered successfully as Admin' : 'Registered successfully');
        
    } catch (PDOException $e) {
        sendError('Registration failed: ' . $e->getMessage(), 500);
    }
    exit();
}

// Login endpoint
if ($method === 'POST' && strpos($path, 'auth/login') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        sendError('Email and password are required');
        exit();
    }
    
    $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    $password = $input['password'];
    
    try {
        $stmt = $conn->prepare("SELECT id, email, password, username, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            sendError('Invalid email or password', 401);
            exit();
        }
        
        $token = generateToken($user['id'], $user['email'], $user['is_admin']);
        
        sendSuccess([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'is_admin' => $user['is_admin']
            ]
        ], 'Login successful');
        
    } catch (PDOException $e) {
        sendError('Login failed: ' . $e->getMessage(), 500);
    }
    exit();
}

// Verify token endpoint
if ($method === 'GET' && strpos($path, 'auth/verify') !== false) {
    $user = getCurrentUser();
    if (!$user) {
        sendError('Invalid or expired token', 401);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, email, username, is_admin FROM users WHERE id = ?");
        $stmt->execute([$user['user_id']]);
        $userData = $stmt->fetch();
        
        if (!$userData) {
            sendError('User not found', 404);
            exit();
        }
        
        sendSuccess([
            'user' => $userData
        ], 'Token valid');
        
    } catch (PDOException $e) {
        sendError('Verification failed: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= POSTS ENDPOINTS =============

// Get all posts or filter by tag/date
if ($method === 'GET' && strpos($path, 'posts') !== false && !isset($_GET['id'])) {
    try {
        $where = ["status = 'published'"];
        $params = [];
        
        // Filter by tag
        if (isset($_GET['tag'])) {
            $where[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON pt.tag_id = t.id WHERE pt.post_id = p.id AND t.name = ?)";
            $params[] = $_GET['tag'];
        }
        
        // Filter by year
        if (isset($_GET['year'])) {
            $where[] = "YEAR(p.created_at) = ?";
            $params[] = intval($_GET['year']);
        }
        
        // Filter by month
        if (isset($_GET['month'])) {
            $where[] = "MONTH(p.created_at) = ?";
            $params[] = intval($_GET['month']);
        }
        
        // Search
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $where[] = "(p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT p.*, u.username as author_name, u.email as author_email,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM view_logs WHERE post_id = p.id) as view_count
                FROM posts p
                JOIN users u ON p.author_id = u.id
                WHERE $whereClause
                ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();
        
        // Get tags for each post
        foreach ($posts as &$post) {
            $stmt = $conn->prepare("SELECT t.* FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = ?");
            $stmt->execute([$post['id']]);
            $post['tags'] = $stmt->fetchAll();
        }
        
        sendSuccess($posts);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch posts: ' . $e->getMessage(), 500);
    }
    exit();
}

// Get single post by ID
if ($method === 'GET' && strpos($path, 'posts') !== false && isset($_GET['id'])) {
    try {
        $postId = intval($_GET['id']);
        
        $stmt = $conn->prepare("SELECT p.*, u.username as author_name, u.email as author_email,
                                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                                (SELECT COUNT(*) FROM view_logs WHERE post_id = p.id) as view_count
                                FROM posts p
                                JOIN users u ON p.author_id = u.id
                                WHERE p.id = ? AND p.status = 'published'");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();
        
        if (!$post) {
            sendError('Post not found', 404);
            exit();
        }
        
        // Get tags
        $stmt = $conn->prepare("SELECT t.* FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = ?");
        $stmt->execute([$postId]);
        $post['tags'] = $stmt->fetchAll();
        
        // Log view
        $user = getCurrentUser();
        $userId = $user ? $user['user_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO view_logs (post_id, user_id, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $userId, $ipAddress]);
        
        sendSuccess($post);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch post: ' . $e->getMessage(), 500);
    }
    exit();
}

// Create post (Admin only)
if ($method === 'POST' && strpos($path, 'posts') !== false) {
    $user = requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['title']) || !isset($input['content'])) {
        sendError('Title and content are required');
        exit();
    }
    
    try {
        $title = htmlspecialchars($input['title']);
        $content = strip_tags($input['content'], '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre><a>');
        $status = isset($input['status']) ? $input['status'] : 'published';
        $tags = isset($input['tags']) ? $input['tags'] : [];
        
        $stmt = $conn->prepare("INSERT INTO posts (title, content, author_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $user['user_id'], $status]);
        
        $postId = $conn->lastInsertId();
        
        // Add tags
        foreach ($tags as $tagName) {
            $tagName = htmlspecialchars(trim($tagName));
            if (empty($tagName)) continue;
            
            $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
            $stmt->execute([$tagName]);
            
            $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();
            
            $stmt = $conn->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$postId, $tag['id']]);
        }
        
        sendSuccess(['id' => $postId], 'Post created successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to create post: ' . $e->getMessage(), 500);
    }
    exit();
}

// Update post (Admin only)
if ($method === 'PUT' && strpos($path, 'posts') !== false) {
    $user = requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        sendError('Post ID is required');
        exit();
    }
    
    try {
        $postId = intval($input['id']);
        
        // Check if post exists
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            sendError('Post not found', 404);
            exit();
        }
        
        $updates = [];
        $params = [];
        
        if (isset($input['title'])) {
            $updates[] = "title = ?";
            $params[] = htmlspecialchars($input['title']);
        }
        
        if (isset($input['content'])) {
            $updates[] = "content = ?";
            $params[] = strip_tags($input['content'], '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre><a>');
        }
        
        if (isset($input['status'])) {
            $updates[] = "status = ?";
            $params[] = $input['status'];
        }
        
        if (!empty($updates)) {
            $params[] = $postId;
            $sql = "UPDATE posts SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        // Update tags if provided
        if (isset($input['tags'])) {
            $stmt = $conn->prepare("DELETE FROM post_tags WHERE post_id = ?");
            $stmt->execute([$postId]);
            
            foreach ($input['tags'] as $tagName) {
                $tagName = htmlspecialchars(trim($tagName));
                if (empty($tagName)) continue;
                
                $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
                $stmt->execute([$tagName]);
                
                $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
                $stmt->execute([$tagName]);
                $tag = $stmt->fetch();
                
                $stmt = $conn->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$postId, $tag['id']]);
            }
        }
        
        sendSuccess([], 'Post updated successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to update post: ' . $e->getMessage(), 500);
    }
    exit();
}

// Delete post (Admin only)
if ($method === 'DELETE' && strpos($path, 'posts') !== false) {
    $user = requireAdmin();
    
    if (!isset($_GET['id'])) {
        sendError('Post ID is required');
        exit();
    }
    
    try {
        $postId = intval($_GET['id']);
        
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        
        if ($stmt->rowCount() === 0) {
            sendError('Post not found', 404);
            exit();
        }
        
        sendSuccess([], 'Post deleted successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to delete post: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= COMMENTS ENDPOINTS =============

// Get comments for a post
if ($method === 'GET' && strpos($path, 'comments') !== false && isset($_GET['post_id'])) {
    try {
        $postId = intval($_GET['post_id']);
        
        $stmt = $conn->prepare("SELECT c.*, u.username, u.email 
                                FROM comments c 
                                JOIN users u ON c.user_id = u.id 
                                WHERE c.post_id = ? 
                                ORDER BY c.created_at DESC");
        $stmt->execute([$postId]);
        $comments = $stmt->fetchAll();
        
        sendSuccess($comments);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch comments: ' . $e->getMessage(), 500);
    }
    exit();
}

// Add comment
if ($method === 'POST' && strpos($path, 'comments') !== false) {
    $user = getCurrentUser();
    if (!$user) {
        sendError('Login required', 401);
        exit();
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['post_id']) || !isset($input['content'])) {
        sendError('Post ID and content are required');
        exit();
    }
    
    try {
        $postId = intval($input['post_id']);
        $content = htmlspecialchars($input['content']);
        
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            sendError('Post not found', 404);
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $user['user_id'], $content]);
        
        $commentId = $conn->lastInsertId();
        
        sendSuccess(['id' => $commentId], 'Comment added successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to add comment: ' . $e->getMessage(), 500);
    }
    exit();
}

// Delete comment
if ($method === 'DELETE' && strpos($path, 'comments') !== false) {
    $user = getCurrentUser();
    if (!$user) {
        sendError('Login required', 401);
        exit();
    }
    
    if (!isset($_GET['id'])) {
        sendError('Comment ID is required');
        exit();
    }
    
    try {
        $commentId = intval($_GET['id']);
        
        $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            sendError('Comment not found', 404);
            exit();
        }
        
        if ($comment['user_id'] != $user['user_id'] && !$user['is_admin']) {
            sendError('Unauthorized', 403);
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        
        sendSuccess([], 'Comment deleted successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to delete comment: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= LIKES ENDPOINTS =============

// Toggle like
if ($method === 'POST' && strpos($path, 'likes') !== false) {
    $user = getCurrentUser();
    if (!$user) {
        sendError('Login required', 401);
        exit();
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['post_id'])) {
        sendError('Post ID is required');
        exit();
    }
    
    try {
        $postId = intval($input['post_id']);
        
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            sendError('Post not found', 404);
            exit();
        }
        
        $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $user['user_id']]);
        $existingLike = $stmt->fetch();
        
        if ($existingLike) {
            $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $user['user_id']]);
            sendSuccess(['liked' => false], 'Post unliked');
        } else {
            $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$postId, $user['user_id']]);
            sendSuccess(['liked' => true], 'Post liked');
        }
        
    } catch (PDOException $e) {
        sendError('Failed to toggle like: ' . $e->getMessage(), 500);
    }
    exit();
}

// Get like status
if ($method === 'GET' && strpos($path, 'likes') !== false && isset($_GET['post_id'])) {
    $user = getCurrentUser();
    
    try {
        $postId = intval($_GET['post_id']);
        
        $stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?");
        $stmt->execute([$postId]);
        $result = $stmt->fetch();
        
        $liked = false;
        if ($user) {
            $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $user['user_id']]);
            $liked = $stmt->fetch() ? true : false;
        }
        
        sendSuccess([
            'like_count' => $result['like_count'],
            'liked' => $liked
        ]);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch like status: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= TAGS ENDPOINTS =============

if ($method === 'GET' && strpos($path, 'tags') !== false) {
    try {
        $stmt = $conn->query("SELECT t.*, COUNT(pt.post_id) as post_count
                             FROM tags t
                             LEFT JOIN post_tags pt ON t.id = pt.tag_id
                             GROUP BY t.id
                             ORDER BY post_count DESC, t.name ASC");
        $tags = $stmt->fetchAll();
        
        sendSuccess($tags);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch tags: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= ADMIN ENDPOINTS =============

// Get all users
if ($method === 'GET' && strpos($path, 'admin/users') !== false) {
    $user = requireAdmin();
    
    try {
        $stmt = $conn->prepare("SELECT id, email, username, is_admin, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        sendSuccess($users);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch users: ' . $e->getMessage(), 500);
    }
    exit();
}

// Delete user
if ($method === 'DELETE' && strpos($path, 'admin/users') !== false) {
    $user = requireAdmin();
    
    if (!isset($_GET['id'])) {
        sendError('User ID is required');
        exit();
    }
    
    try {
        $userId = intval($_GET['id']);
        
        if ($userId == $user['user_id']) {
            sendError('Cannot delete your own account', 400);
            exit();
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() === 0) {
            sendError('User not found', 404);
            exit();
        }
        
        sendSuccess([], 'User deleted successfully');
        
    } catch (PDOException $e) {
        sendError('Failed to delete user: ' . $e->getMessage(), 500);
    }
    exit();
}

// Get view logs
if ($method === 'GET' && strpos($path, 'admin/view-logs') !== false) {
    $user = requireAdmin();
    
    try {
        $where = [];
        $params = [];
        
        if (isset($_GET['post_id'])) {
            $where[] = "vl.post_id = ?";
            $params[] = intval($_GET['post_id']);
        }
        
        if (isset($_GET['user_id'])) {
            $where[] = "vl.user_id = ?";
            $params[] = intval($_GET['user_id']);
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $conn->prepare("SELECT vl.*, p.title as post_title, u.username, u.email 
                                FROM view_logs vl
                                JOIN posts p ON vl.post_id = p.id
                                LEFT JOIN users u ON vl.user_id = u.id
                                $whereClause
                                ORDER BY vl.viewed_at DESC
                                LIMIT 1000");
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        sendSuccess($logs);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch view logs: ' . $e->getMessage(), 500);
    }
    exit();
}

// Get statistics
if ($method === 'GET' && strpos($path, 'admin/stats') !== false) {
    $user = requireAdmin();
    
    try {
        $stats = [];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM posts");
        $stats['total_posts'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM comments");
        $stats['total_comments'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM likes");
        $stats['total_likes'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM view_logs");
        $stats['total_views'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT p.id, p.title, COUNT(vl.id) as view_count
                             FROM posts p
                             LEFT JOIN view_logs vl ON p.id = vl.post_id
                             GROUP BY p.id
                             ORDER BY view_count DESC
                             LIMIT 10");
        $stats['most_viewed_posts'] = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT p.id, p.title, COUNT(l.id) as like_count
                             FROM posts p
                             LEFT JOIN likes l ON p.id = l.post_id
                             GROUP BY p.id
                             ORDER BY like_count DESC
                             LIMIT 10");
        $stats['most_liked_posts'] = $stmt->fetchAll();
        
        sendSuccess($stats);
        
    } catch (PDOException $e) {
        sendError('Failed to fetch statistics: ' . $e->getMessage(), 500);
    }
    exit();
}

// ============= DEFAULT / API DOCUMENTATION =============

// API Documentation
echo json_encode([
    'name' => 'Hugo 開發日誌 API',
    'version' => '1.0',
    'endpoints' => [
        'Authentication' => [
            'POST /auth/register' => 'Register new user (first user becomes admin)',
            'POST /auth/login' => 'Login with email and password',
            'GET /auth/verify' => 'Verify token'
        ],
        'Posts' => [
            'GET /posts' => 'Get all posts (supports filters: tag, year, month, search)',
            'GET /posts?id={id}' => 'Get single post by ID',
            'POST /posts' => 'Create post (admin only)',
            'PUT /posts' => 'Update post (admin only)',
            'DELETE /posts?id={id}' => 'Delete post (admin only)'
        ],
        'Comments' => [
            'GET /comments?post_id={id}' => 'Get comments for a post',
            'POST /comments' => 'Add comment (requires login)',
            'DELETE /comments?id={id}' => 'Delete comment (owner or admin)'
        ],
        'Likes' => [
            'GET /likes?post_id={id}' => 'Get like status for a post',
            'POST /likes' => 'Toggle like on a post (requires login)'
        ],
        'Tags' => [
            'GET /tags' => 'Get all tags with post counts'
        ],
        'Admin' => [
            'GET /admin/users' => 'Get all users (admin only)',
            'DELETE /admin/users?id={id}' => 'Delete user (admin only)',
            'GET /admin/view-logs' => 'Get view logs (admin only)',
            'GET /admin/stats' => 'Get statistics (admin only)'
        ]
    ],
    'database' => [
        'status' => 'MySQL database configured',
        'setup' => 'Run init.php to create tables',
        'note' => 'Visit init.php to initialize the database'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
