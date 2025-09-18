<?php
// WebSocket signaling server for video conferencing
// This is a simplified implementation for demonstration

class SignalingServer {
    private $clients = [];
    private $rooms = [];
    
    public function __construct() {
        // Initialize WebSocket server
        $this->startServer();
    }
    
    private function startServer() {
        // In a real implementation, you would use ReactPHP or similar
        // For this demo, we'll use a simple polling mechanism
        $this->handleRequest();
    }
    
    private function handleRequest() {
        $action = $_POST['action'] ?? '';
        $roomId = $_POST['room_id'] ?? '';
        $userId = $_POST['user_id'] ?? '';
        $message = $_POST['message'] ?? '';
        
        switch ($action) {
            case 'join':
                $this->joinRoom($roomId, $userId);
                break;
            case 'leave':
                $this->leaveRoom($roomId, $userId);
                break;
            case 'offer':
                $this->handleOffer($roomId, $userId, $message);
                break;
            case 'answer':
                $this->handleAnswer($roomId, $userId, $message);
                break;
            case 'ice-candidate':
                $this->handleIceCandidate($roomId, $userId, $message);
                break;
            case 'get-participants':
                $this->getParticipants($roomId);
                break;
        }
    }
    
    private function joinRoom($roomId, $userId) {
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }
        
        $this->rooms[$roomId][$userId] = [
            'id' => $userId,
            'joined_at' => time(),
            'status' => 'active'
        ];
        
        // Notify other participants
        $this->broadcastToRoom($roomId, [
            'type' => 'user-joined',
            'user_id' => $userId,
            'participants' => array_keys($this->rooms[$roomId])
        ], $userId);
        
        echo json_encode(['success' => true, 'participants' => array_keys($this->rooms[$roomId])]);
    }
    
    private function leaveRoom($roomId, $userId) {
        if (isset($this->rooms[$roomId][$userId])) {
            unset($this->rooms[$roomId][$userId]);
            
            // Notify other participants
            $this->broadcastToRoom($roomId, [
                'type' => 'user-left',
                'user_id' => $userId,
                'participants' => array_keys($this->rooms[$roomId])
            ], $userId);
        }
        
        echo json_encode(['success' => true]);
    }
    
    private function handleOffer($roomId, $userId, $offer) {
        $this->broadcastToRoom($roomId, [
            'type' => 'offer',
            'from' => $userId,
            'offer' => $offer
        ], $userId);
        
        echo json_encode(['success' => true]);
    }
    
    private function handleAnswer($roomId, $userId, $answer) {
        $this->broadcastToRoom($roomId, [
            'type' => 'answer',
            'from' => $userId,
            'answer' => $answer
        ], $userId);
        
        echo json_encode(['success' => true]);
    }
    
    private function handleIceCandidate($roomId, $userId, $candidate) {
        $this->broadcastToRoom($roomId, [
            'type' => 'ice-candidate',
            'from' => $userId,
            'candidate' => $candidate
        ], $userId);
        
        echo json_encode(['success' => true]);
    }
    
    private function getParticipants($roomId) {
        $participants = isset($this->rooms[$roomId]) ? array_keys($this->rooms[$roomId]) : [];
        echo json_encode(['success' => true, 'participants' => $participants]);
    }
    
    private function broadcastToRoom($roomId, $message, $excludeUserId = null) {
        // In a real implementation, this would send to WebSocket clients
        // For this demo, we'll store messages in a file-based queue
        $messageFile = "signaling_messages_{$roomId}.json";
        $messages = [];
        
        if (file_exists($messageFile)) {
            $messages = json_decode(file_get_contents($messageFile), true) ?: [];
        }
        
        $messages[] = [
            'timestamp' => time(),
            'message' => $message,
            'exclude' => $excludeUserId
        ];
        
        file_put_contents($messageFile, json_encode($messages));
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server = new SignalingServer();
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
