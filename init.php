<?php
/**
 * Hugo 開發日誌 Database Initialization
 * Run this file once to set up the database tables
 * 
 * SECURITY NOTE: This file contains hardcoded database credentials for a demo/development
 * InfinityFree hosting account. In a production environment:
 * 1. Move credentials to environment variables
 * 2. Use getenv() to read DB_HOST, DB_USER, DB_PASS, DB_NAME
 * 3. Delete or protect this file after database initialization
 */

// Database Configuration
// TODO: Move these to environment variables in production
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_39929369');
define('DB_PASS', 'hfy23whc');
define('DB_NAME', 'if0_39929369_blog');
define('DB_PORT', '3306');

// Connect to database
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h1>Hugo 開發日誌 - Database Initialization</h1>";
    echo "<p>Connected to database successfully!</p>";
    echo "<hr>";
    
    // SQL statements for creating tables
    $sqlStatements = [
        // Users Table
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            username VARCHAR(100) NOT NULL,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Posts Table
        "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            author_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('draft', 'published') DEFAULT 'published',
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_created_at (created_at),
            INDEX idx_author (author_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tags Table
        "CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Post Tags Junction Table
        "CREATE TABLE IF NOT EXISTS post_tags (
            post_id INT NOT NULL,
            tag_id INT NOT NULL,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Comments Table
        "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_post (post_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Likes Table
        "CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (post_id, user_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // View Logs Table
        "CREATE TABLE IF NOT EXISTS view_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT,
            ip_address VARCHAR(45),
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_post (post_id),
            INDEX idx_viewed_at (viewed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    $tableNames = [
        'users',
        'posts',
        'tags',
        'post_tags',
        'comments',
        'likes',
        'view_logs'
    ];
    
    echo "<h2>Creating Tables...</h2>";
    echo "<ul>";
    
    foreach ($sqlStatements as $index => $sql) {
        try {
            $conn->exec($sql);
            echo "<li style='color: green;'>✓ Table <strong>{$tableNames[$index]}</strong> created successfully</li>";
        } catch (PDOException $e) {
            echo "<li style='color: red;'>✗ Error creating table <strong>{$tableNames[$index]}</strong>: " . $e->getMessage() . "</li>";
        }
    }
    
    echo "</ul>";
    echo "<hr>";
    
    // Check if tables were created
    echo "<h2>Verifying Tables...</h2>";
    echo "<ul>";
    
    foreach ($tableNames as $table) {
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                // Get row count - table name is from controlled array, safe to use
                $countStmt = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $countStmt->fetch()['count'];
                echo "<li style='color: green;'>✓ Table <strong>$table</strong> exists (contains $count rows)</li>";
            } else {
                echo "<li style='color: orange;'>⚠ Table <strong>$table</strong> not found</li>";
            }
        } catch (PDOException $e) {
            echo "<li style='color: red;'>✗ Error checking table <strong>$table</strong>: " . $e->getMessage() . "</li>";
        }
    }
    
    echo "</ul>";
    echo "<hr>";
    
    echo "<h2>Database Schema</h2>";
    echo "<pre>";
    echo "Hugo 開發日誌 Database Schema\n\n";
    echo "Tables Created:\n";
    echo "1. users         - User accounts with admin flag\n";
    echo "2. posts         - Blog posts with metadata\n";
    echo "3. tags          - Tag definitions\n";
    echo "4. post_tags     - Post-tag relationships\n";
    echo "5. comments      - User comments on posts\n";
    echo "6. likes         - Post like tracking\n";
    echo "7. view_logs     - Post view analytics\n";
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Database Initialization Complete!</h2>";
    echo "<p>You can now use the API at <a href='api.php'>api.php</a></p>";
    echo "<p>Next steps:</p>";
    echo "<ol>";
    echo "<li>Open your frontend (index.html)</li>";
    echo "<li>Register your first account (will become admin)</li>";
    echo "<li>Start creating posts!</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<p style='color: gray; font-size: 0.9em;'>";
    echo "Note: This script creates tables only if they don't exist. ";
    echo "You can run it multiple times safely. ";
    echo "For security, consider deleting or protecting this file after initialization.";
    echo "</p>";
    
} catch(PDOException $e) {
    echo "<h1 style='color: red;'>Database Connection Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h2>Troubleshooting:</h2>";
    echo "<ul>";
    echo "<li>Check database credentials in this file</li>";
    echo "<li>Ensure the database exists on the server</li>";
    echo "<li>Verify network connectivity to database server</li>";
    echo "<li>Check if your hosting allows external database connections</li>";
    echo "</ul>";
}
?>
