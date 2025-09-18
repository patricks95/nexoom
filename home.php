<?php
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexoom - Video Conferencing</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>') no-repeat center center;
            background-size: cover;
            opacity: 0.3;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px 0;
            z-index: 100;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: white;
            text-decoration: none;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .main-content {
            text-align: center;
            color: white;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ffffff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 50px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .meeting-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .action-card:hover::before {
            opacity: 1;
        }
        
        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .action-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
            position: relative;
            z-index: 10;
        }
        
        .action-icon.start {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .action-icon.join {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .action-icon.admin {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .action-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 10;
        }
        
        .action-description {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 25px;
            line-height: 1.6;
            position: relative;
            z-index: 10;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            color: #1f2937;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            z-index: 10;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .meeting-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            margin-top: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .form-group {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 16px;
            outline: none;
            backdrop-filter: blur(10px);
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .features {
            margin-top: 80px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            text-align: center;
            color: white;
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .feature-description {
            font-size: 14px;
            opacity: 0.7;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .meeting-actions {
                grid-template-columns: 1fr;
            }
            
            .form-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="hero-bg"></div>
        
        <div class="header">
            <div class="nav">
                <a href="#" class="logo">Nexoom</a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="container">
            <div class="main-content">
                <h1 class="hero-title">Professional Video Conferencing</h1>
                <p class="hero-subtitle">Connect, collaborate, and communicate with crystal-clear quality</p>
                
                <div class="meeting-actions">
                    <div class="action-card">
                        <div class="action-icon start">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="action-title">Start Meeting</h3>
                        <p class="action-description">Create a new video meeting and invite participants. Perfect for presentations and team collaboration.</p>
                        <a href="simple-meeting.php?room=meeting_<?php echo uniqid(); ?>" class="action-btn">
                            <i class="fas fa-play"></i> Start Now
                        </a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon join">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="action-title">Join Meeting</h3>
                        <p class="action-description">Enter a meeting ID to join an existing video conference. Quick and easy access to any meeting.</p>
                        <a href="simple-meeting.php?room=demo_meeting" class="action-btn">
                            <i class="fas fa-sign-in-alt"></i> Join Demo
                        </a>
                    </div>
                    
                    <?php if ($user['role'] === 'admin'): ?>
                    <div class="action-card">
                        <div class="action-icon admin">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3 class="action-title">Admin Panel</h3>
                        <p class="action-description">Manage users, meetings, and system settings. Full administrative control over your video conferencing platform.</p>
                        <a href="admin.php" class="action-btn">
                            <i class="fas fa-tools"></i> Manage
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="meeting-form">
                    <h3 class="form-title">Join Specific Meeting</h3>
                    <form method="GET" action="simple-meeting.php">
                        <div class="form-group">
                            <input type="text" name="room" placeholder="Enter Meeting ID" class="form-input" required>
                            <button type="submit" class="form-btn">
                                <i class="fas fa-arrow-right"></i> Join
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h4 class="feature-title">HD Video</h4>
                        <p class="feature-description">Crystal clear video quality for professional meetings</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <h4 class="feature-title">Clear Audio</h4>
                        <p class="feature-description">Advanced audio processing for perfect sound quality</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h4 class="feature-title">Screen Share</h4>
                        <p class="feature-description">Share your screen with participants seamlessly</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4 class="feature-title">Live Chat</h4>
                        <p class="feature-description">Real-time messaging during video meetings</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
