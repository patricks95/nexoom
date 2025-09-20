<?php
// Test VideoSDK domain accessibility
echo "Testing VideoSDK domain accessibility...\n\n";

$domains = [
    'https://videosdk.live',
    'https://meet.videosdk.live',
    'https://app.videosdk.live',
    'https://prebuilt.videosdk.live'
];

foreach ($domains as $domain) {
    echo "Testing: $domain\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $domain);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  Error: $error\n";
    } else {
        echo "  HTTP Code: $httpCode\n";
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "  Status: ✅ Accessible\n";
        } else {
            echo "  Status: ❌ Not accessible\n";
        }
    }
    echo "\n";
}

// Test with a simple meeting URL
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiI3MzY0ODk5My1iZWZkLTQwYzMtYmE3MS01NmEzZDFlNmUzMDQiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0OTQ1NSwiZXhwIjoxNzg5ODg1NDU1fQ.6iIQeg2rABa0Mp3gfUxqsSxd6J8GBuyQ6tP7msoPuJU';
$meetingId = 'test_meeting_' . uniqid();
$participantName = 'Test User';

$testUrls = [
    "https://videosdk.live/?token=$token&meetingId=$meetingId&name=" . urlencode($participantName),
    "https://meet.videosdk.live/?token=$token&meetingId=$meetingId&name=" . urlencode($participantName),
    "https://app.videosdk.live/?token=$token&meetingId=$meetingId&name=" . urlencode($participantName),
    "https://prebuilt.videosdk.live/?token=$token&meetingId=$meetingId&name=" . urlencode($participantName)
];

echo "Testing VideoSDK meeting URLs...\n\n";

foreach ($testUrls as $url) {
    echo "Testing: " . substr($url, 0, 80) . "...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  Error: $error\n";
    } else {
        echo "  HTTP Code: $httpCode\n";
        if ($httpCode >= 200 && $httpCode < 400) {
            echo "  Status: ✅ Working\n";
        } else {
            echo "  Status: ❌ Not working\n";
        }
    }
    echo "\n";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Domain Test - Nexoom</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        pre { background: #000; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>VideoSDK Domain Test</h1>
    <pre><?php
        echo "Testing VideoSDK domain accessibility...\n\n";

        $domains = [
            'https://videosdk.live',
            'https://meet.videosdk.live',
            'https://app.videosdk.live',
            'https://prebuilt.videosdk.live'
        ];

        foreach ($domains as $domain) {
            echo "Testing: $domain\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "  Error: $error\n";
            } else {
                echo "  HTTP Code: $httpCode\n";
                if ($httpCode >= 200 && $httpCode < 400) {
                    echo "  Status: ✅ Accessible\n";
                } else {
                    echo "  Status: ❌ Not accessible\n";
                }
            }
            echo "\n";
        }
    ?></pre>
    
    <h2>Test Meeting</h2>
    <p><a href="videosdk-react.php?meetingId=test_meeting_123&name=Test%20User" target="_blank">Test VideoSDK React Meeting</a></p>
</body>
</html>
