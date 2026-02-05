<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// Get all posts or filter by tag/date
if ($method === 'GET' && !isset($_GET['id'])) {
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
if ($method === 'GET' && isset($_GET['id'])) {
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
if ($method === 'POST') {
    $user = requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['title']) || !isset($input['content'])) {
        sendError('Title and content are required');
        exit();
    }
    
    try {
        $title = htmlspecialchars($input['title']);
        // Basic HTML sanitization - strip dangerous tags but allow safe formatting
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
            
            // Insert or get tag
            $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
            $stmt->execute([$tagName]);
            
            $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();
            
            // Link tag to post
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
if ($method === 'PUT') {
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
            // Basic HTML sanitization - strip dangerous tags but allow safe formatting
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
            // Remove old tags
            $stmt = $conn->prepare("DELETE FROM post_tags WHERE post_id = ?");
            $stmt->execute([$postId]);
            
            // Add new tags
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
if ($method === 'DELETE') {
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

sendError('Invalid request method', 405);
?>
