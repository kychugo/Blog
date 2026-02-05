<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// All admin endpoints require admin access
$user = requireAdmin();

// Get all users
if ($method === 'GET' && strpos($_SERVER['REQUEST_URI'], '/users') !== false) {
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
if ($method === 'DELETE' && strpos($_SERVER['REQUEST_URI'], '/users') !== false) {
    if (!isset($_GET['id'])) {
        sendError('User ID is required');
        exit();
    }
    
    try {
        $userId = intval($_GET['id']);
        
        // Prevent deleting yourself
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
if ($method === 'GET' && strpos($_SERVER['REQUEST_URI'], '/view-logs') !== false) {
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
if ($method === 'GET' && strpos($_SERVER['REQUEST_URI'], '/stats') !== false) {
    try {
        $stats = [];
        
        // Total users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Total posts
        $stmt = $conn->query("SELECT COUNT(*) as count FROM posts");
        $stats['total_posts'] = $stmt->fetch()['count'];
        
        // Total comments
        $stmt = $conn->query("SELECT COUNT(*) as count FROM comments");
        $stats['total_comments'] = $stmt->fetch()['count'];
        
        // Total likes
        $stmt = $conn->query("SELECT COUNT(*) as count FROM likes");
        $stats['total_likes'] = $stmt->fetch()['count'];
        
        // Total views
        $stmt = $conn->query("SELECT COUNT(*) as count FROM view_logs");
        $stats['total_views'] = $stmt->fetch()['count'];
        
        // Most viewed posts
        $stmt = $conn->query("SELECT p.id, p.title, COUNT(vl.id) as view_count
                             FROM posts p
                             LEFT JOIN view_logs vl ON p.id = vl.post_id
                             GROUP BY p.id
                             ORDER BY view_count DESC
                             LIMIT 10");
        $stats['most_viewed_posts'] = $stmt->fetchAll();
        
        // Most liked posts
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

sendError('Invalid endpoint', 404);
?>
