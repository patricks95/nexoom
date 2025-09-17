<?php
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error_message = 'Please fill in all fields';
        } else {
            $result = $auth->login($username, $password);
            if ($result['success']) {
                header('Location: index.php');
                exit();
            } else {
                $error_message = $result['message'];
            }
        }
    } elseif ($_POST['action'] === 'register') {
        $username = trim($_POST['reg_username'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirm_password = $_POST['reg_confirm_password'] ?? '';
        $full_name = trim($_POST['reg_full_name'] ?? '');
        $role = $_POST['reg_role'] ?? 'viewer';
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error_message = 'Please fill in all fields';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error_message = 'Password must be at least 6 characters long';
        } else {
            $result = $auth->register($username, $email, $password, $full_name, $role);
            if ($result['success']) {
                $success_message = 'Registration successful! Please login.';
            } else {
                $error_message = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nexoom Video Conferencing</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-4">Nexoom</h1>
            <p class="text-xl text-white opacity-90">Professional Video Conferencing Platform</p>
        </div>
        
        <div class="max-w-md mx-auto">
            <!-- Login Form -->
            <div class="bg-white rounded-2xl p-8 card-hover mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>
                
                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                            Username or Email
                        </label>
                        <input type="text" id="username" name="username" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your username or email">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </button>
                </form>
            </div>
            
            <!-- Registration Form -->
            <div class="bg-white rounded-2xl p-8 card-hover">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Register</h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_full_name">
                            Full Name
                        </label>
                        <input type="text" id="reg_full_name" name="reg_full_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_username">
                            Username
                        </label>
                        <input type="text" id="reg_username" name="reg_username" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Choose a username">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_email">
                            Email
                        </label>
                        <input type="email" id="reg_email" name="reg_email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_role">
                            Role
                        </label>
                        <select id="reg_role" name="reg_role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="viewer">Viewer</option>
                            <option value="broadcaster">Broadcaster</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_password">
                            Password
                        </label>
                        <input type="password" id="reg_password" name="reg_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Choose a password (min 6 characters)">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reg_confirm_password">
                            Confirm Password
                        </label>
                        <input type="password" id="reg_confirm_password" name="reg_confirm_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Confirm your password">
                    </div>
                    
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-user-plus mr-2"></i> Register
                    </button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <p class="text-white opacity-75">Powered by Jitsi Meet</p>
        </div>
    </div>
</body>
</html>
