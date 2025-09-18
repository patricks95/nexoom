<?php
// VideoSDK Token Generation Endpoint
// This generates authentication tokens for VideoSDK meetings

require_once 'includes/auth.php';

// Only allow logged-in users
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user = getCurrentUser();
$meetingId = $_POST['meetingId'] ?? '';

if (empty($meetingId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Meeting ID is required']);
    exit();
}

// VideoSDK Configuration
$apiKey = '0fc8e1a5-c073-407c-9bf4-153442433432';
$secretKey = '208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e';

// Generate token
function generateVideoSDKToken($apiKey, $secretKey, $meetingId, $participantId, $role = 'host') {
    $payload = [
        'apikey' => $apiKey,
        'permissions' => [
            'allowJoin' => true,
            'allowModerate' => $role === 'host' || $role === 'admin',
            'allowRecord' => $role === 'host' || $role === 'admin'
        ],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60), // 24 hours
        'iss' => $apiKey,
        'sub' => $meetingId,
        'roomId' => $meetingId,
        'participantId' => $participantId,
        'role' => $role
    ];
    
    // Create JWT token
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secretKey, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
}

// Generate token for user
$role = $user['role'] === 'broadcaster' || $user['role'] === 'admin' ? 'host' : 'participant';
$token = generateVideoSDKToken($apiKey, $secretKey, $meetingId, $user['id'], $role);

// Return token
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'token' => $token,
    'meetingId' => $meetingId,
    'participantId' => $user['id'],
    'role' => $role
]);
?>
