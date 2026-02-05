<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// Toggle like on a post
if ($method === 'POST') {
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
        
        // Check if post exists
        $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            sendError('Post not found', 404);
            exit();
        }
        
        // Check if already liked
        $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $user['user_id']]);
        $existingLike = $stmt->fetch();
        
        if ($existingLike) {
            // Unlike
            $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $user['user_id']]);
            sendSuccess(['liked' => false], 'Post unliked');
        } else {
            // Like
            $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$postId, $user['user_id']]);
            sendSuccess(['liked' => true], 'Post liked');
        }
        
    } catch (PDOException $e) {
        sendError('Failed to toggle like: ' . $e->getMessage(), 500);
    }
    exit();
}

// Get like status for a post
if ($method === 'GET' && isset($_GET['post_id'])) {
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

sendError('Invalid request method', 405);
?>
