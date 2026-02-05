<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// Get all tags with post counts
if ($method === 'GET') {
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

sendError('Invalid request method', 405);
?>
