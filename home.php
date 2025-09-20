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
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
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
            font-size: 32px;
            font-weight: 800;
            color: #d4af37;
            text-decoration: none;
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #d4af37;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37, #b8941f);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #1a3d2e;
            border: 3px solid #4a7c59;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: 3px solid #ef4444;
            padding: 12px 25px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(15px);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.3);
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }
        
        .main-content {
            text-align: center;
            color: #d4af37;
        }
        
        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #d4af37, #b8941f, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            text-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .hero-subtitle {
            font-size: 1.6rem;
            margin-bottom: 60px;
            opacity: 0.9;
            font-weight: 400;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .meeting-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .action-card {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            padding: 45px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid #4a7c59;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.1));
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .action-card:hover::before {
            opacity: 1;
        }
        
        .action-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: #d4af37;
        }
        
        .action-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 25px;
            position: relative;
            z-index: 10;
            border: 4px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .action-icon.start {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
        }
        
        .action-icon.join {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
        }
        
        .action-icon.admin {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
        }
        
        .action-title {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 10;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .action-description {
            font-size: 17px;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.7;
            position: relative;
            z-index: 10;
            color: #ffffff;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            border: 3px solid #4a7c59;
            padding: 18px 35px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            position: relative;
            z-index: 10;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .action-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
            background: linear-gradient(135deg, #b8941f, #d4af37);
        }
        
        .meeting-form {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            padding: 45px;
            margin-top: 60px;
            border: 3px solid #4a7c59;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .form-title {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 35px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-input {
            flex: 1;
            padding: 18px 25px;
            border: 3px solid #4a7c59;
            border-radius: 30px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            font-size: 18px;
            outline: none;
            backdrop-filter: blur(15px);
            font-weight: 600;
        }
        
        .form-input::placeholder {
            color: rgba(212, 175, 55, 0.6);
            font-weight: 600;
        }
        
        .form-input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }
        
        .form-btn {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: 3px solid #ef4444;
            padding: 18px 35px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }
        
        .form-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(220, 38, 38, 0.4);
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .features {
            margin-top: 80px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            text-align: center;
            color: #d4af37;
            padding: 30px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            border-radius: 20px;
            border: 2px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .feature-item:hover {
            transform: translateY(-10px);
            border-color: #d4af37;
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.2);
        }
        
        .feature-icon {
            font-size: 52px;
            margin-bottom: 25px;
            color: #d4af37;
            text-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }
        
        .feature-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .feature-description {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.7;
            color: #ffffff;
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
                        <a href="videosdk-join.php?meetingId=meeting_<?php echo uniqid(); ?>" class="action-btn">
                            <i class="fas fa-play"></i> Start Now
                        </a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon join">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="action-title">Join Meeting</h3>
                        <p class="action-description">Enter a meeting ID to join an existing video conference. Quick and easy access to any meeting.</p>
                        <a href="videosdk-join.php?meetingId=demo_meeting" class="action-btn">
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
                    <form method="GET" action="videosdk-join.php">
                        <div class="form-group">
                            <input type="text" name="meetingId" placeholder="Enter Meeting ID" class="form-input" required>
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
