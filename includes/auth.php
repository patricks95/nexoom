<?php
// Authentication system for Nexoom Video Conferencing Platform

session_start();
require_once 'config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Register a new user
    public function register($username, $email, $password, $full_name, $role = 'viewer') {
        try {
            // Check if user already exists
            if ($this->userExists($username, $email)) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to register user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Login user
    public function login($username, $password) {
        try {
            $sql = "SELECT id, username, email, password_hash, full_name, role, is_active FROM users WHERE (username = ? OR email = ?) AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Create session
                $this->createSession($user['id']);
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'message' => 'Login successful', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Logout user
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Remove session from database
            $this->removeSession($_SESSION['user_id']);
        }
        
        // Destroy session
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role']
        ];
    }
    
    // Check if user exists
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Create user session
    private function createSession($user_id) {
        $session_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id, $session_token, $ip_address, $user_agent, $expires_at]);
        
        return $session_token;
    }
    
    // Remove user session
    private function removeSession($user_id) {
        $sql = "UPDATE user_sessions SET is_active = FALSE WHERE user_id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
    }
    
    // Check if user has permission
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $user_role = $_SESSION['role'];
        
        switch ($permission) {
            case 'create_meeting':
                return in_array($user_role, ['admin', 'broadcaster']);
            case 'join_meeting':
                return in_array($user_role, ['admin', 'broadcaster', 'viewer']);
            case 'manage_users':
                return $user_role === 'admin';
            case 'manage_meetings':
                return in_array($user_role, ['admin', 'broadcaster']);
            case 'admin_access':
                return $user_role === 'admin';
            default:
                return false;
        }
    }
    
    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    // Require specific role
    public function requireRole($required_roles) {
        $this->requireLogin();
        
        if (!in_array($_SESSION['role'], $required_roles)) {
            header('Location: unauthorized.php');
            exit();
        }
    }
}

// Global auth instance
$auth = new Auth();

// Helper functions
function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}

function hasPermission($permission) {
    global $auth;
    return $auth->hasPermission($permission);
}

function requireLogin() {
    global $auth;
    $auth->requireLogin();
}

function requireRole($required_roles) {
    global $auth;
    $auth->requireRole($required_roles);
}
?>
