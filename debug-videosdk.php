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

// Test VideoSDK API directly
echo "<h1>VideoSDK Debug Information</h1>";

// Test 1: Generate Token
echo "<h2>1. Token Generation Test</h2>";
try {
    $token = $videoSDK->generateToken();
    echo "<p><strong>Success:</strong> Token generated</p>";
    echo "<p><strong>Token:</strong> " . substr($token, 0, 100) . "...</p>";
    
    // Decode token to verify
    $parts = explode('.', $token);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        echo "<p><strong>Token Payload:</strong> " . json_encode($payload, JSON_PRETTY_PRINT) . "</p>";
    }
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

// Test 2: Test API Connection
echo "<h2>2. API Connection Test</h2>";
$testMeetingId = 'test_' . uniqid();
$result = $videoSDK->createMeeting($testMeetingId);

echo "<p><strong>Meeting ID:</strong> $testMeetingId</p>";
echo "<p><strong>Result:</strong> " . json_encode($result, JSON_PRETTY_PRINT) . "</p>";

// Test 3: Test with cURL directly
echo "<h2>3. Direct cURL Test</h2>";
$token = $videoSDK->generateToken();
$url = 'https://api.videosdk.live/v2/rooms';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['customRoomId' => 'test_direct_' . uniqid()]));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>cURL Error:</strong> " . ($error ?: 'None') . "</p>";
echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";

// Test 4: Check if we can use the provided token directly
echo "<h2>4. Using Provided Token Test</h2>";
$providedToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiIwZmM4ZTFhNS1jMDczLTQwN2MtOWJmNC0xNTM0NDI0MzM0MzIiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODMwMTAwMiwiZXhwIjoxNzg5ODM3MDAyfQ.fJDgzB4C2cdU9zi7i5uBHt6tGnhFzEqmLeRajtcONrM';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $providedToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['customRoomId' => 'test_provided_' . uniqid()]));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>cURL Error:</strong> " . ($error ?: 'None') . "</p>";
echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";

// Test 5: Check API documentation format
echo "<h2>5. Alternative API Format Test</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.videosdk.live/v2/rooms');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $providedToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['customRoomId' => 'test_bearer_' . uniqid()]));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>cURL Error:</strong> " . ($error ?: 'None') . "</p>";
echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";

echo "<hr>";
echo "<p><a href='home.php'>Back to Home</a></p>";
?>
