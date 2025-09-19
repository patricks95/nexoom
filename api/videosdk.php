<?php
// VideoSDK API endpoints for Nexoom Video Conferencing Platform

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/auth.php';
require_once '../includes/videosdk.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api/videosdk' from path parts
$pathParts = array_slice($pathParts, 2);

$action = $pathParts[0] ?? '';
$meetingId = $pathParts[1] ?? '';

try {
    switch ($action) {
        case 'create':
            if ($method === 'POST') {
                createMeeting();
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'validate':
            if ($method === 'GET') {
                validateMeeting($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'get':
            if ($method === 'GET') {
                getMeeting($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'end':
            if ($method === 'POST') {
                endMeeting($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'participants':
            if ($method === 'GET') {
                getParticipants($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'remove-participant':
            if ($method === 'DELETE') {
                $participantId = $pathParts[2] ?? '';
                removeParticipant($meetingId, $participantId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'start-recording':
            if ($method === 'POST') {
                startRecording($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'stop-recording':
            if ($method === 'POST') {
                stopRecording($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'start-livestream':
            if ($method === 'POST') {
                startLiveStream($meetingId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'stop-livestream':
            if ($method === 'POST') {
                $streamId = $pathParts[2] ?? '';
                stopLiveStream($meetingId, $streamId);
            } else {
                methodNotAllowed();
            }
            break;
            
        case 'token':
            if ($method === 'GET') {
                generateToken();
            } else {
                methodNotAllowed();
            }
            break;
            
        default:
            notFound();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}

function createMeeting() {
    global $user;
    
    // Check permissions
    if (!hasPermission('create_meeting')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $meetingId = $input['meetingId'] ?? null;
    $customRoomId = $input['customRoomId'] ?? null;
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->createMeeting($meetingId, $customRoomId);
    
    if ($result['success']) {
        http_response_code(201);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function validateMeeting($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->validateMeeting($meetingId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
}

function getMeeting($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->getMeeting($meetingId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
}

function endMeeting($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->endMeeting($meetingId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function getParticipants($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->getParticipants($meetingId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function removeParticipant($meetingId, $participantId) {
    if (empty($meetingId) || empty($participantId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID and Participant ID are required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->removeParticipant($meetingId, $participantId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function startRecording($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $webhookUrl = $input['webhookUrl'] ?? null;
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->startRecording($meetingId, $webhookUrl);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function stopRecording($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->stopRecording($meetingId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function startLiveStream($meetingId) {
    if (empty($meetingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID is required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $rtmpUrls = $input['rtmpUrls'] ?? [];
    
    if (empty($rtmpUrls)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'RTMP URLs are required']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->startLiveStream($meetingId, $rtmpUrls);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function stopLiveStream($meetingId, $streamId) {
    if (empty($meetingId) || empty($streamId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Meeting ID and Stream ID are required']);
        return;
    }
    
    // Check permissions
    if (!hasPermission('manage_meetings')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        return;
    }
    
    $videoSDK = getVideoSDK();
    $result = $videoSDK->stopLiveStream($meetingId, $streamId);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
}

function generateToken() {
    $videoSDK = getVideoSDK();
    $token = $videoSDK->generateToken();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => $token,
        'expires_in' => 86400 // 24 hours
    ]);
}

function methodNotAllowed() {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function notFound() {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
?>
