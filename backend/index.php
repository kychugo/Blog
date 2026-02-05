<?php
/**
 * Hugo 開發日誌 API Router
 * 
 * Simple routing for the blog API
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($scriptName, '', $requestUri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Route requests to appropriate handlers
if (strpos($path, 'auth/register') !== false || strpos($path, 'auth/login') !== false || strpos($path, 'auth/verify') !== false) {
    require_once 'auth.php';
} elseif (strpos($path, 'posts') !== false) {
    require_once 'posts.php';
} elseif (strpos($path, 'comments') !== false) {
    require_once 'comments.php';
} elseif (strpos($path, 'likes') !== false) {
    require_once 'likes.php';
} elseif (strpos($path, 'tags') !== false) {
    require_once 'tags.php';
} elseif (strpos($path, 'admin') !== false) {
    require_once 'admin.php';
} else {
    require_once 'config.php';
    
    // API Documentation
    header('Content-Type: application/json');
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
            'host' => 'sql201.infinityfree.com',
            'database' => 'if0_39929369_blog',
            'setup' => 'Import database.sql to create tables'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
