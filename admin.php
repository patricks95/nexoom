<?php
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();

// Check if user is admin
if ($user['role'] !== 'admin') {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Nexoom</title>
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
        
        .admin-container {
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
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
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 15px;
        }
        
        .stat-icon.users {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .stat-icon.meetings {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .stat-icon.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .stat-icon.recordings {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .admin-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .admin-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .card-icon.manage-users {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .card-icon.manage-meetings {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .card-icon.system-settings {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .card-icon.analytics {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .card-description {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .card-btn {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            color: #1f2937;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .recent-activity {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .activity-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 12px;
            opacity: 0.6;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 10px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <div class="header-content">
                <div class="logo">Admin Panel</div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <a href="home.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number">24</div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon meetings">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-number">156</div>
                <div class="stat-label">Meetings Created</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="stat-number">8</div>
                <div class="stat-label">Active Meetings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon recordings">
                    <i class="fas fa-record-vinyl"></i>
                </div>
                <div class="stat-number">42</div>
                <div class="stat-label">Recordings</div>
            </div>
        </div>
        
        <div class="admin-sections">
            <div class="admin-card">
                <div class="card-header">
                    <div class="card-icon manage-users">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="card-title">Manage Users</div>
                </div>
                <div class="card-description">
                    View, edit, and manage user accounts. Create new users, modify permissions, and monitor user activity.
                </div>
                <a href="#" class="card-btn">
                    <i class="fas fa-arrow-right"></i> Manage Users
                </a>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <div class="card-icon manage-meetings">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="card-title">Manage Meetings</div>
                </div>
                <div class="card-description">
                    Monitor active meetings, view meeting history, and manage meeting settings. End meetings if needed.
                </div>
                <a href="#" class="card-btn">
                    <i class="fas fa-arrow-right"></i> Manage Meetings
                </a>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <div class="card-icon system-settings">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="card-title">System Settings</div>
                </div>
                <div class="card-description">
                    Configure system-wide settings, manage VideoSDK credentials, and adjust platform preferences.
                </div>
                <a href="#" class="card-btn">
                    <i class="fas fa-arrow-right"></i> Settings
                </a>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <div class="card-icon analytics">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="card-title">Analytics</div>
                </div>
                <div class="card-description">
                    View detailed analytics, usage statistics, and performance metrics for your video conferencing platform.
                </div>
                <a href="#" class="card-btn">
                    <i class="fas fa-arrow-right"></i> View Analytics
                </a>
            </div>
        </div>
        
        <div class="recent-activity">
            <h3 class="activity-title">Recent Activity</h3>
            
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">New meeting created by John Doe</div>
                    <div class="activity-time">2 minutes ago</div>
                </div>
            </div>
            
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">New user registered: Jane Smith</div>
                    <div class="activity-time">15 minutes ago</div>
                </div>
            </div>
            
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">Meeting ended: Team Standup</div>
                    <div class="activity-time">1 hour ago</div>
                </div>
            </div>
            
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">System settings updated</div>
                    <div class="activity-time">2 hours ago</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>