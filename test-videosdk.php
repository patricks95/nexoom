<?php
require_once 'includes/auth.php';
require_once 'includes/videosdk.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$videoSDK = getVideoSDK();

// Test VideoSDK functionality
$testResults = [];

// Test 1: Generate Token
try {
    $token = $videoSDK->generateToken();
    $testResults['token'] = [
        'success' => true,
        'message' => 'Token generated successfully',
        'token' => substr($token, 0, 50) . '...'
    ];
} catch (Exception $e) {
    $testResults['token'] = [
        'success' => false,
        'message' => 'Token generation failed: ' . $e->getMessage()
    ];
}

// Test 2: Create Meeting
try {
    $meetingId = 'test_meeting_' . uniqid();
    $result = $videoSDK->createMeeting($meetingId);
    $testResults['create_meeting'] = $result;
} catch (Exception $e) {
    $testResults['create_meeting'] = [
        'success' => false,
        'message' => 'Create meeting failed: ' . $e->getMessage()
    ];
}

// Test 3: Get Frontend Config
try {
    $config = $videoSDK->getFrontendConfig('test_meeting', $user['full_name'], $user['id']);
    $testResults['frontend_config'] = [
        'success' => true,
        'message' => 'Frontend config generated successfully',
        'config_keys' => array_keys($config)
    ];
} catch (Exception $e) {
    $testResults['frontend_config'] = [
        'success' => false,
        'message' => 'Frontend config failed: ' . $e->getMessage()
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoSDK Test - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .test-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .test-title {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .test-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
        }
        
        .test-item.success {
            border-left-color: #10b981;
        }
        
        .test-item.error {
            border-left-color: #ef4444;
        }
        
        .test-name {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .test-message {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .test-details {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            font-family: monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
            word-break: break-all;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        .status-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        .status-icon.success {
            background: #10b981;
        }
        
        .status-icon.error {
            background: #ef4444;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="test-title">VideoSDK Integration Test</h1>
        
        <div class="test-card">
            <h2 style="color: white; margin-bottom: 20px;">Test Results</h2>
            
            <?php foreach ($testResults as $testName => $result): ?>
            <div class="test-item <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <div class="test-name">
                    <span class="status-icon <?php echo $result['success'] ? 'success' : 'error'; ?>"></span>
                    <?php echo ucwords(str_replace('_', ' ', $testName)); ?>
                </div>
                <div class="test-message"><?php echo htmlspecialchars($result['message']); ?></div>
                <?php if (isset($result['token'])): ?>
                <div class="test-details">Token: <?php echo htmlspecialchars($result['token']); ?></div>
                <?php endif; ?>
                <?php if (isset($result['config_keys'])): ?>
                <div class="test-details">Config Keys: <?php echo implode(', ', $result['config_keys']); ?></div>
                <?php endif; ?>
                <?php if (isset($result['meetingId'])): ?>
                <div class="test-details">Meeting ID: <?php echo htmlspecialchars($result['meetingId']); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="test-card">
            <h2 style="color: white; margin-bottom: 20px;">API Configuration</h2>
            <div class="test-item">
                <div class="test-name">API Key</div>
                <div class="test-details">0fc8e1a5-c073-407c-9bf4-153442433432</div>
            </div>
            <div class="test-item">
                <div class="test-name">Secret Key</div>
                <div class="test-details">208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e</div>
            </div>
            <div class="test-item">
                <div class="test-name">Base URL</div>
                <div class="test-details">https://api.videosdk.live/v2</div>
            </div>
        </div>
        
        <div class="test-card">
            <h2 style="color: white; margin-bottom: 20px;">Quick Actions</h2>
            <a href="videosdk-meeting.php?room=test_meeting_<?php echo uniqid(); ?>" class="back-btn">
                <i class="fas fa-video"></i> Test Video Meeting
            </a>
            <a href="home.php" class="back-btn" style="margin-left: 15px;">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>
