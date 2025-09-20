<?php
// Direct VideoSDK test - opens VideoSDK URL directly
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'meeting_' . uniqid();
$participantName = isset($_GET['name']) ? $_GET['name'] : 'Test User';

// Use the provided token directly
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiI3MzY0ODk5My1iZWZkLTQwYzMtYmE3MS01NmEzZDFlNmUzMDQiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0OTQ1NSwiZXhwIjoxNzg5ODg1NDU1fQ.6iIQeg2rABa0Mp3gfUxqsSxd6J8GBuyQ6tP7msoPuJU';

// Build VideoSDK URL
$videoSDKUrl = 'https://videosdk.live/?' . http_build_query([
    'token' => $token,
    'meetingId' => $meetingId,
    'name' => $participantName,
    'micEnabled' => 'true',
    'webcamEnabled' => 'true',
    'chatEnabled' => 'true',
    'screenShareEnabled' => 'true',
    'recordingEnabled' => 'false',
    'liveStreamEnabled' => 'false',
    'whiteboardEnabled' => 'false',
    'raiseHandEnabled' => 'false',
    'participantCanToggleSelfWebcam' => 'true',
    'participantCanToggleSelfMic' => 'true',
    'participantCanLeave' => 'true',
    'participantCanEndMeeting' => 'false',
    'joinScreenEnabled' => 'true',
    'joinScreenTitle' => 'Nexoom Video Meeting',
    'brandingEnabled' => 'false',
    'brandName' => 'Nexoom',
    'poweredBy' => 'false',
    'redirectOnLeave' => 'index.php',
    'layoutType' => 'GRID',
    'maxResolution' => 'hd',
    'debug' => 'true'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct VideoSDK Test - Nexoom</title>
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
        
        .iframe-container {
            margin-top: 20px;
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid #4a7c59;
        }
        
        .meeting-iframe {
            width: 100%;
            height: 600px;
            border: none;
            background: #000;
        }
        
        .actions {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Direct VideoSDK Test</h1>
        
        <div class="info-section">
            <div class="info-title">Meeting ID:</div>
            <div class="info-content"><?php echo htmlspecialchars($meetingId); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Participant Name:</div>
            <div class="info-content"><?php echo htmlspecialchars($participantName); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Generated VideoSDK URL:</div>
            <div class="info-content"><?php echo htmlspecialchars($videoSDKUrl); ?></div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Token (First 50 chars):</div>
            <div class="info-content"><?php echo htmlspecialchars(substr($token, 0, 50)) . '...'; ?></div>
        </div>
        
        <div class="actions">
            <a href="<?php echo htmlspecialchars($videoSDKUrl); ?>" target="_blank" class="btn btn-primary">
                <i class="fas fa-external-link-alt"></i> Open in New Tab
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
        
        <div class="iframe-container">
            <iframe 
                class="meeting-iframe"
                src="<?php echo htmlspecialchars($videoSDKUrl); ?>"
                allow="camera; microphone; display-capture; autoplay"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</body>
</html>
