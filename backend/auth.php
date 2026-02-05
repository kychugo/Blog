<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

// Register endpoint
if ($method === 'POST' && strpos($_SERVER['REQUEST_URI'], '/register') !== false) {
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
if ($method === 'POST' && strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
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
if ($method === 'GET' && strpos($_SERVER['REQUEST_URI'], '/verify') !== false) {
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

sendError('Invalid endpoint', 404);
?>
