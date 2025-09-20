<?php
require_once 'includes/auth.php';
require_once 'includes/videosdk.php';
require_once 'includes/videosdk-config.php';

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

// Initialize VideoSDK
$videoSDK = new VideoSDK('0fc8e1a5-c073-407c-9bf4-153442433432', '208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e');

// Handle form submissions
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['create_meeting'])) {
        $meetingId = $_POST['meeting_id'] ?: 'meeting_' . uniqid();
        $result = $videoSDK->createMeeting($meetingId);
        
        if ($result['success']) {
            $message = 'Meeting created successfully! Meeting ID: ' . $result['meetingId'];
            $messageType = 'success';
        } else {
            $message = 'Failed to create meeting: ' . $result['message'];
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['validate_meeting'])) {
        $meetingId = $_POST['meeting_id'];
        $result = $videoSDK->validateMeeting($meetingId);
        
        if ($result['success']) {
            $message = 'Meeting is valid and active!';
            $messageType = 'success';
        } else {
            $message = 'Meeting validation failed: ' . $result['message'];
            $messageType = 'error';
        }
    }
}

// Get configuration options
$layoutTypes = VideoSDKConfig::getLayoutTypes();
$resolutions = VideoSDKConfig::getResolutions();
$regions = VideoSDKConfig::getRegions();
$features = VideoSDKConfig::getFeatureDescriptions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoSDK Admin - Nexoom</title>
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
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 30px;
            border: 3px solid #4a7c59;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        
        .admin-title {
            font-size: 48px;
            font-weight: 800;
            color: #d4af37;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .admin-subtitle {
            font-size: 20px;
            color: #a0a0a0;
            font-weight: 600;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .admin-card {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 25px;
            padding: 30px;
            border: 3px solid #4a7c59;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .admin-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: #d4af37;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #d4af37;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 3px solid #4a7c59;
            border-radius: 15px;
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
            transform: translateY(-2px);
        }
        
        .form-input::placeholder {
            color: #a0a0a0;
        }
        
        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 3px solid #4a7c59;
            border-radius: 15px;
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .form-select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            border: 3px solid #4a7c59;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(220, 38, 38, 0.4);
        }
        
        .message {
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
        }
        
        .message-success {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            border: 3px solid #4a7c59;
        }
        
        .message-error {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: 3px solid #ef4444;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 15px;
            border: 2px solid #4a7c59;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            border-color: #d4af37;
            transform: translateY(-2px);
        }
        
        .feature-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #d4af37;
        }
        
        .feature-label {
            color: #d4af37;
            font-weight: 600;
            font-size: 14px;
            flex: 1;
        }
        
        .feature-description {
            color: #a0a0a0;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            border: 3px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #d4af37;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 16px;
            color: #a0a0a0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="home.php" class="back-btn btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <div class="admin-header">
            <h1 class="admin-title">VideoSDK Admin Panel</h1>
            <p class="admin-subtitle">Manage VideoSDK meetings and configuration</p>
        </div>
        
        <?php if ($message): ?>
        <div class="message message-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $videoSDK->getApiKey(); ?></div>
                <div class="stat-label">API Key</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($regions); ?></div>
                <div class="stat-label">Available Regions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($features); ?></div>
                <div class="stat-label">Configurable Features</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($layoutTypes); ?></div>
                <div class="stat-label">Layout Types</div>
            </div>
        </div>
        
        <div class="admin-grid">
            <!-- Create Meeting -->
            <div class="admin-card">
                <h2 class="card-title">Create New Meeting</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="meeting_id">Meeting ID</label>
                        <input 
                            type="text" 
                            id="meeting_id" 
                            name="meeting_id" 
                            class="form-input" 
                            placeholder="Leave empty for auto-generated"
                        >
                    </div>
                    <button type="submit" name="create_meeting" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Meeting
                    </button>
                </form>
            </div>
            
            <!-- Validate Meeting -->
            <div class="admin-card">
                <h2 class="card-title">Validate Meeting</h2>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="validate_meeting_id">Meeting ID</label>
                        <input 
                            type="text" 
                            id="validate_meeting_id" 
                            name="meeting_id" 
                            class="form-input" 
                            placeholder="Enter Meeting ID to validate"
                            required
                        >
                    </div>
                    <button type="submit" name="validate_meeting" class="btn btn-secondary">
                        <i class="fas fa-check"></i> Validate Meeting
                    </button>
                </form>
            </div>
            
            <!-- Quick Join -->
            <div class="admin-card">
                <h2 class="card-title">Quick Join</h2>
                <p style="color: #a0a0a0; margin-bottom: 20px;">Join a meeting with default settings</p>
                <a href="videosdk-join.php" class="btn btn-primary">
                    <i class="fas fa-video"></i> Join Meeting
                </a>
            </div>
            
            <!-- Configuration -->
            <div class="admin-card">
                <h2 class="card-title">Default Configuration</h2>
                <p style="color: #a0a0a0; margin-bottom: 20px;">Configure default VideoSDK settings</p>
                <a href="videosdk-config.php" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Configure Settings
                </a>
            </div>
        </div>
        
        <!-- Feature Configuration -->
        <div class="admin-card">
            <h2 class="card-title">Available Features</h2>
            <p style="color: #a0a0a0; margin-bottom: 20px;">All configurable features from VideoSDK React prebuilt UI</p>
            
            <div class="feature-grid">
                <?php foreach ($features as $key => $description): ?>
                <div class="feature-item">
                    <input type="checkbox" id="<?php echo $key; ?>" class="feature-checkbox">
                    <div>
                        <div class="feature-label"><?php echo ucwords(str_replace('_', ' ', $key)); ?></div>
                        <div class="feature-description"><?php echo htmlspecialchars($description); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- API Information -->
        <div class="admin-card">
            <h2 class="card-title">API Information</h2>
            <div style="background: linear-gradient(135deg, #2d5a3d, #1a4d3a); padding: 20px; border-radius: 15px; border: 2px solid #4a7c59;">
                <p style="color: #d4af37; margin-bottom: 10px;"><strong>API Key:</strong> <?php echo $videoSDK->getApiKey(); ?></p>
                <p style="color: #d4af37; margin-bottom: 10px;"><strong>Base URL:</strong> https://api.videosdk.live</p>
                <p style="color: #d4af37; margin-bottom: 10px;"><strong>Meeting URL:</strong> https://meet.videosdk.live</p>
                <p style="color: #a0a0a0; margin-bottom: 0;"><strong>Documentation:</strong> <a href="https://docs.videosdk.live" target="_blank" style="color: #d4af37;">https://docs.videosdk.live</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-generate meeting ID
        document.getElementById('meeting_id').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'meeting_' + Math.random().toString(36).substr(2, 9);
            }
        });
        
        // Copy meeting ID to validation field
        document.getElementById('meeting_id').addEventListener('input', function() {
            document.getElementById('validate_meeting_id').value = this.value;
        });
    </script>
</body>
</html>
