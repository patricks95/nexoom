<?php
echo "PHP Debug Test\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";

// Test VideoSDK
require_once 'includes/videosdk.php';

$videoSDK = new VideoSDK('73648993-befd-40c3-ba71-56a3d1e6e304', 'f604fad113af89b72ae9df09d9ca9a4bfa83a36b0e28543668ab947f0e40b02e');

$meetingId = 'test_meeting_' . uniqid();
$participantName = 'Test User';
$participantId = 'test_participant_' . uniqid();

$config = $videoSDK->getFrontendConfig($meetingId, $participantName, $participantId);

echo "\nVideoSDK Test:\n";
echo "Meeting ID: " . $meetingId . "\n";
echo "Participant Name: " . $participantName . "\n";
echo "Token (first 50 chars): " . substr($config['token'], 0, 50) . "...\n";

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
    'debug' => 'false'
];

// Build URL with parameters
$videoSDKUrl = 'https://videosdk.live/?' . http_build_query($urlParams);

echo "\nGenerated VideoSDK URL:\n";
echo $videoSDKUrl . "\n";

echo "\nURL Length: " . strlen($videoSDKUrl) . " characters\n";
echo "URL Parameters Count: " . count($urlParams) . "\n";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug - Nexoom</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        pre { background: #000; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>PHP Debug Information</h1>
    <pre><?php
        echo "PHP Debug Test\n";
        echo "Current time: " . date('Y-m-d H:i:s') . "\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        
        echo "\nVideoSDK Test:\n";
        echo "Meeting ID: " . $meetingId . "\n";
        echo "Participant Name: " . $participantName . "\n";
        echo "Token (first 50 chars): " . substr($config['token'], 0, 50) . "...\n";
        
        echo "\nGenerated VideoSDK URL:\n";
        echo $videoSDKUrl . "\n";
        
        echo "\nURL Length: " . strlen($videoSDKUrl) . " characters\n";
        echo "URL Parameters Count: " . count($urlParams) . "\n";
    ?></pre>
    
    <h2>Test Meeting</h2>
    <p><a href="<?php echo $videoSDKUrl; ?>" target="_blank">Open VideoSDK Meeting in New Tab</a></p>
    
    <h2>Test Static Meeting Page</h2>
    <p><a href="videosdk-meeting-static.php?meetingId=<?php echo $meetingId; ?>&name=<?php echo urlencode($participantName); ?>">Open Static Meeting Page</a></p>
</body>
</html>
