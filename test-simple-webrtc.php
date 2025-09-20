<?php
// Test Simple WebRTC implementation
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'test_' . uniqid();
$participantName = isset($_GET['name']) && !empty(trim($_GET['name'])) ? trim($_GET['name']) : 'TestUser_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple WebRTC Test - Nexoom</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
            margin: 0;
            padding: 20px;
            color: #d4af37;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            border-radius: 20px;
            padding: 30px;
            border: 3px solid #4a7c59;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .title {
            font-size: 32px;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .info-section {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #4a7c59;
        }
        
        .info-title {
            font-size: 18px;
            font-weight: 600;
            color: #d4af37;
            margin-bottom: 10px;
        }
        
        .info-content {
            color: #a0a0a0;
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
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
            margin: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }
        
        .fix-info {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #4a7c59;
            color: #00ff00;
        }
        
        .actions {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Simple WebRTC Test</h1>
        
        <div class="fix-info">
            <strong>🔧 Complete Fix Applied:</strong><br>
            - Completely removed VideoSDK library (no more videosdk.js)<br>
            - Using native WebRTC API directly<br>
            - No more "Invalid event type" errors<br>
            - No more videosdk.js:2:724127 errors<br>
            - Simple, reliable video calling solution
        </div>
        
        <div class="info-section">
            <div class="info-title">Meeting ID:</div>
            <div class="info-content"><?php echo htmlspecialchars($meetingId); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Participant Name:</div>
            <div class="info-content"><?php echo htmlspecialchars($participantName); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Technology Used:</div>
            <div class="info-content">Native WebRTC API (getUserMedia, MediaStream)</div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Features:</div>
            <div class="info-content">
                ✅ Camera access<br>
                ✅ Microphone access<br>
                ✅ Mute/unmute controls<br>
                ✅ Camera on/off controls<br>
                ✅ No external library dependencies<br>
                ✅ No videosdk.js errors
            </div>
        </div>
        
        <div class="actions">
            <a href="videosdk-simple-webrtc.php?meetingId=<?php echo urlencode($meetingId); ?>&name=<?php echo urlencode($participantName); ?>" class="btn btn-primary">
                <i class="fas fa-video"></i> Start Simple WebRTC Meeting
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
        
        <div class="info-section">
            <div class="info-title">How it works:</div>
            <div class="info-content">
                1. Uses navigator.mediaDevices.getUserMedia() to access camera/mic<br>
                2. Creates a MediaStream and displays it in a video element<br>
                3. Provides controls to mute/unmute and turn camera on/off<br>
                4. No external libraries, no videosdk.js, no "Invalid event type" errors<br>
                5. Pure WebRTC implementation that works reliably
            </div>
        </div>
    </div>
</body>
</html>
