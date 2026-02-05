<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// Get comments for a post
if ($method === 'GET' && isset($_GET['post_id'])) {
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

// Add comment (requires login)
if ($method === 'POST') {
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
        
        // Check if post exists
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

// Delete comment (Admin or comment owner)
if ($method === 'DELETE') {
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
        
        // Check if user owns the comment or is admin
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

sendError('Invalid request method', 405);
?>
