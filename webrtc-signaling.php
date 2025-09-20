<?php
// Simple signaling server for WebRTC meetings
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'join':
        handleJoin();
        break;
    case 'offer':
        handleOffer();
        break;
    case 'answer':
        handleAnswer();
        break;
    case 'ice-candidate':
        handleIceCandidate();
        break;
    case 'leave':
        handleLeave();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleJoin() {
    $roomId = $_GET['room'] ?? '';
    $participantId = $_GET['participant'] ?? uniqid();
    $participantName = $_GET['name'] ?? 'Anonymous';
    
    if (empty($roomId)) {
        echo json_encode(['error' => 'Room ID required']);
        return;
    }
    
    // Store participant info in session or database
    session_start();
    if (!isset($_SESSION['rooms'])) {
        $_SESSION['rooms'] = [];
    }
    
    if (!isset($_SESSION['rooms'][$roomId])) {
        $_SESSION['rooms'][$roomId] = [];
    }
    
    $_SESSION['rooms'][$roomId][$participantId] = [
        'name' => $participantName,
        'joined_at' => time()
    ];
    
    echo json_encode([
        'success' => true,
        'participantId' => $participantId,
        'participants' => $_SESSION['rooms'][$roomId]
    ]);
}

function handleOffer() {
    $roomId = $_GET['room'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $offer = $_POST['offer'] ?? '';
    
    if (empty($roomId) || empty($from) || empty($to) || empty($offer)) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    // Store offer for the target participant
    session_start();
    if (!isset($_SESSION['offers'])) {
        $_SESSION['offers'] = [];
    }
    
    $_SESSION['offers'][$to] = [
        'from' => $from,
        'offer' => $offer,
        'timestamp' => time()
    ];
    
    echo json_encode(['success' => true]);
}

function handleAnswer() {
    $roomId = $_GET['room'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $answer = $_POST['answer'] ?? '';
    
    if (empty($roomId) || empty($from) || empty($to) || empty($answer)) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    // Store answer for the target participant
    session_start();
    if (!isset($_SESSION['answers'])) {
        $_SESSION['answers'] = [];
    }
    
    $_SESSION['answers'][$to] = [
        'from' => $from,
        'answer' => $answer,
        'timestamp' => time()
    ];
    
    echo json_encode(['success' => true]);
}

function handleIceCandidate() {
    $roomId = $_GET['room'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $candidate = $_POST['candidate'] ?? '';
    
    if (empty($roomId) || empty($from) || empty($to) || empty($candidate)) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    // Store ICE candidate for the target participant
    session_start();
    if (!isset($_SESSION['ice_candidates'])) {
        $_SESSION['ice_candidates'] = [];
    }
    
    if (!isset($_SESSION['ice_candidates'][$to])) {
        $_SESSION['ice_candidates'][$to] = [];
    }
    
    $_SESSION['ice_candidates'][$to][] = [
        'from' => $from,
        'candidate' => $candidate,
        'timestamp' => time()
    ];
    
    echo json_encode(['success' => true]);
}

function handleLeave() {
    $roomId = $_GET['room'] ?? '';
    $participantId = $_GET['participant'] ?? '';
    
    if (empty($roomId) || empty($participantId)) {
        echo json_encode(['error' => 'Missing parameters']);
        return;
    }
    
    session_start();
    if (isset($_SESSION['rooms'][$roomId][$participantId])) {
        unset($_SESSION['rooms'][$roomId][$participantId]);
    }
    
    echo json_encode(['success' => true]);
}
?>
