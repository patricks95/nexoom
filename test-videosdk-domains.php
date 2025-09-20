<?php
// Test VideoSDK domains based on official documentation
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'meeting_' . uniqid();
$participantName = isset($_GET['name']) && !empty(trim($_GET['name'])) ? trim($_GET['name']) : 'TestUser_' . uniqid();

// Use the provided token directly
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiI3MzY0ODk5My1iZWZkLTQwYzMtYmE3MS01NmEzZDFlNmUzMDQiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0OTQ1NSwiZXhwIjoxNzg5ODg1NDU1fQ.6iIQeg2rABa0Mp3gfUxqsSxd6J8GBuyQ6tP7msoPuJU';

// Test different VideoSDK domains
$domains = [
    'https://meet.videosdk.live',
    'https://videosdk.live',
    'https://app.videosdk.live',
    'https://prebuilt.videosdk.live'
];

// Build test URLs
$testUrls = [];
foreach ($domains as $domain) {
    $testUrls[] = $domain . '/?token=' . $token . '&meetingId=' . $meetingId . '&name=' . urlencode($participantName) . '&micEnabled=true&webcamEnabled=true&chatEnabled=true&screenShareEnabled=true&recordingEnabled=false&liveStreamEnabled=false&whiteboardEnabled=false&raiseHandEnabled=false&participantCanToggleSelfWebcam=true&participantCanToggleSelfMic=true&participantCanLeave=true&participantCanEndMeeting=false&joinScreenEnabled=true&joinScreenTitle=Nexoom+Video+Meeting&brandingEnabled=false&brandName=Nexoom&poweredBy=false&redirectOnLeave=index.php&layoutType=GRID&maxResolution=hd&debug=false';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoSDK Domain Test - Nexoom</title>
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
        
        .domain-test {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .domain-card {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 15px;
            padding: 20px;
            border: 2px solid #4a7c59;
        }
        
        .domain-name {
            font-size: 16px;
            font-weight: 600;
            color: #d4af37;
            margin-bottom: 10px;
        }
        
        .domain-url {
            color: #a0a0a0;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .actions {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">VideoSDK Domain Test</h1>
        
        <div class="info-section">
            <div class="info-title">Meeting ID:</div>
            <div class="info-content"><?php echo htmlspecialchars($meetingId); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Participant Name:</div>
            <div class="info-content"><?php echo htmlspecialchars($participantName); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Token (First 50 chars):</div>
            <div class="info-content"><?php echo htmlspecialchars(substr($token, 0, 50)) . '...'; ?></div>
        </div>
        
        <h2 style="color: #d4af37; margin: 30px 0 20px 0;">Test Different VideoSDK Domains</h2>
        
        <div class="domain-test">
            <?php foreach ($domains as $index => $domain): ?>
            <div class="domain-card">
                <div class="domain-name"><?php echo htmlspecialchars($domain); ?></div>
                <div class="domain-url"><?php echo htmlspecialchars($testUrls[$index]); ?></div>
                <a href="<?php echo htmlspecialchars($testUrls[$index]); ?>" target="_blank" class="btn btn-primary">
                    Test Domain <?php echo $index + 1; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>
