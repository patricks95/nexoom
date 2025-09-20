<?php
require_once 'includes/videosdk.php';

// Test VideoSDK configuration
$videoSDK = new VideoSDK('73648993-befd-40c3-ba71-56a3d1e6e304', 'f604fad113af89b72ae9df09d9ca9a4bfa83a36b0e28543668ab947f0e40b02e');

$meetingId = 'test_meeting_' . uniqid();
$participantName = 'Test User';
$participantId = 'test_participant_' . uniqid();

$config = $videoSDK->getFrontendConfig($meetingId, $participantName, $participantId);

// URL parameters for VideoSDK
$urlParams = [
    'token' => $config['token'],
    'meetingId' => $meetingId,
    'name' => $participantName,
    'participantId' => $participantId,
    'region' => $config['region'],
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
    'joinScreenMeetingUrl' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
    'joinScreenTitle' => 'Nexoom Video Meeting',
    'brandingEnabled' => 'false',
    'brandName' => 'Nexoom',
    'poweredBy' => 'false',
    'redirectOnLeave' => 'index.php',
    'layoutType' => 'GRID',
    'maxResolution' => 'hd',
    'debug' => 'true'
];

// Build URL with parameters
$videoSDKUrl = 'https://meet.videosdk.live/?' . http_build_query($urlParams);
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
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
            min-height: 100vh;
            padding: 20px;
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
            transform: translateY(-3px) scale(1.05);
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">VideoSDK Test Page</h1>
        
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
            <div class="info-content"><?php echo htmlspecialchars(substr($config['token'], 0, 50)) . '...'; ?></div>
        </div>
        
        <div style="text-align: center;">
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