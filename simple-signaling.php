<?php
// Simple file-based signaling system for WebRTC
// This handles real-time communication between participants

class SimpleSignaling {
    private $roomId;
    private $userId;
    
    public function __construct($roomId, $userId) {
        $this->roomId = $roomId;
        $this->userId = $userId;
    }
    
    public function handleRequest() {
        $action = $_POST['action'] ?? '';
        $data = $_POST['data'] ?? '';
        
        switch ($action) {
            case 'join':
                return $this->joinRoom($data);
            case 'leave':
                return $this->leaveRoom();
            case 'offer':
                return $this->sendOffer($data);
            case 'answer':
                return $this->sendAnswer($data);
            case 'ice-candidate':
                return $this->sendIceCandidate($data);
            case 'get-messages':
                return $this->getMessages();
            case 'get-participants':
                return $this->getParticipants();
        }
        
        return ['success' => false, 'error' => 'Invalid action'];
    }
    
    private function joinRoom($data) {
        $participants = $this->getParticipantsFromFile();
        
        $participants[$this->userId] = [
            'id' => $this->userId,
            'name' => $data['name'] ?? 'User',
            'role' => $data['role'] ?? 'participant',
            'joined_at' => time(),
            'status' => 'active'
        ];
        
        $this->saveParticipantsToFile($participants);
        
        // Send join message to other participants
        $this->addMessage([
            'type' => 'user-joined',
            'user_id' => $this->userId,
            'name' => $data['name'] ?? 'User',
            'role' => $data['role'] ?? 'participant',
            'participants' => array_keys($participants)
        ]);
        
        return ['success' => true, 'participants' => array_keys($participants)];
    }
    
    private function leaveRoom() {
        $participants = $this->getParticipantsFromFile();
        
        if (isset($participants[$this->userId])) {
            unset($participants[$this->userId]);
            $this->saveParticipantsToFile($participants);
            
            // Send leave message to other participants
            $this->addMessage([
                'type' => 'user-left',
                'user_id' => $this->userId,
                'participants' => array_keys($participants)
            ]);
        }
        
        return ['success' => true];
    }
    
    private function sendOffer($data) {
        $this->addMessage([
            'type' => 'offer',
            'from' => $this->userId,
            'to' => $data['to'] ?? 'all',
            'offer' => $data['offer']
        ]);
        
        return ['success' => true];
    }
    
    private function sendAnswer($data) {
        $this->addMessage([
            'type' => 'answer',
            'from' => $this->userId,
            'to' => $data['to'] ?? 'all',
            'answer' => $data['answer']
        ]);
        
        return ['success' => true];
    }
    
    private function sendIceCandidate($data) {
        $this->addMessage([
            'type' => 'ice-candidate',
            'from' => $this->userId,
            'to' => $data['to'] ?? 'all',
            'candidate' => $data['candidate']
        ]);
        
        return ['success' => true];
    }
    
    private function getMessages() {
        $messageFile = "signaling_messages_{$this->roomId}.json";
        $messages = [];
        
        if (file_exists($messageFile)) {
            $messages = json_decode(file_get_contents($messageFile), true) ?: [];
        }
        
        // Filter messages for this user
        $userMessages = array_filter($messages, function($msg) {
            return $msg['message']['to'] === 'all' || 
                   $msg['message']['to'] === $this->userId ||
                   $msg['message']['from'] === $this->userId;
        });
        
        return ['success' => true, 'messages' => array_values($userMessages)];
    }
    
    private function getParticipants() {
        $participants = $this->getParticipantsFromFile();
        return ['success' => true, 'participants' => array_keys($participants)];
    }
    
    private function addMessage($message) {
        $messageFile = "signaling_messages_{$this->roomId}.json";
        $messages = [];
        
        if (file_exists($messageFile)) {
            $messages = json_decode(file_get_contents($messageFile), true) ?: [];
        }
        
        $messages[] = [
            'timestamp' => time(),
            'message' => $message
        ];
        
        // Keep only last 100 messages
        if (count($messages) > 100) {
            $messages = array_slice($messages, -100);
        }
        
        file_put_contents($messageFile, json_encode($messages));
    }
    
    private function getParticipantsFromFile() {
        $participantFile = "participants_{$this->roomId}.json";
        
        if (file_exists($participantFile)) {
            return json_decode(file_get_contents($participantFile), true) ?: [];
        }
        
        return [];
    }
    
    private function saveParticipantsToFile($participants) {
        $participantFile = "participants_{$this->roomId}.json";
        file_put_contents($participantFile, json_encode($participants));
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = $_POST['room_id'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($roomId) || empty($userId)) {
        echo json_encode(['success' => false, 'error' => 'Missing room_id or user_id']);
        exit;
    }
    
    $signaling = new SimpleSignaling($roomId, $userId);
    $result = $signaling->handleRequest();
    
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
