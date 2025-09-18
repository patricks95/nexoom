<?php
// WebSocket signaling server using ReactPHP
// This will handle real-time communication between participants

require_once 'vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

class WebSocketSignalingServer {
    private $clients = [];
    private $rooms = [];
    private $loop;
    
    public function __construct() {
        $this->loop = Loop::get();
        $this->startServer();
    }
    
    private function startServer() {
        $socket = new SocketServer('0.0.0.0:8080', [], $this->loop);
        $server = new HttpServer($this->loop, function (ServerRequestInterface $request) {
            return $this->handleRequest($request);
        });
        
        $server->listen($socket);
        echo "WebSocket signaling server running on port 8080\n";
        $this->loop->run();
    }
    
    private function handleRequest(ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        if ($path === '/signaling' && $method === 'POST') {
            return $this->handleSignaling($request);
        }
        
        if ($path === '/signaling' && $method === 'GET') {
            return $this->handleWebSocket($request);
        }
        
        return new Response(404, ['Content-Type' => 'text/plain'], 'Not Found');
    }
    
    private function handleSignaling(ServerRequestInterface $request) {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        
        $action = $data['action'] ?? '';
        $roomId = $data['room_id'] ?? '';
        $userId = $data['user_id'] ?? '';
        $message = $data['message'] ?? '';
        
        switch ($action) {
            case 'join':
                $this->joinRoom($roomId, $userId, $data);
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
        }
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['success' => true]));
    }
    
    private function handleWebSocket(ServerRequestInterface $request) {
        // WebSocket upgrade logic would go here
        // For now, return a simple response
        return new Response(200, ['Content-Type' => 'text/html'], $this->getWebSocketClient());
    }
    
    private function joinRoom($roomId, $userId, $data) {
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = [];
        }
        
        $this->rooms[$roomId][$userId] = [
            'id' => $userId,
            'name' => $data['name'] ?? 'User',
            'role' => $data['role'] ?? 'participant',
            'joined_at' => time(),
            'status' => 'active'
        ];
        
        // Broadcast to all participants in the room
        $this->broadcastToRoom($roomId, [
            'type' => 'user-joined',
            'user_id' => $userId,
            'name' => $data['name'] ?? 'User',
            'role' => $data['role'] ?? 'participant',
            'participants' => array_keys($this->rooms[$roomId])
        ], $userId);
    }
    
    private function leaveRoom($roomId, $userId) {
        if (isset($this->rooms[$roomId][$userId])) {
            unset($this->rooms[$roomId][$userId]);
            
            // Broadcast to remaining participants
            $this->broadcastToRoom($roomId, [
                'type' => 'user-left',
                'user_id' => $userId,
                'participants' => array_keys($this->rooms[$roomId])
            ], $userId);
        }
    }
    
    private function handleOffer($roomId, $userId, $offer) {
        $this->broadcastToRoom($roomId, [
            'type' => 'offer',
            'from' => $userId,
            'offer' => $offer
        ], $userId);
    }
    
    private function handleAnswer($roomId, $userId, $answer) {
        $this->broadcastToRoom($roomId, [
            'type' => 'answer',
            'from' => $userId,
            'answer' => $answer
        ], $userId);
    }
    
    private function handleIceCandidate($roomId, $userId, $candidate) {
        $this->broadcastToRoom($roomId, [
            'type' => 'ice-candidate',
            'from' => $userId,
            'candidate' => $candidate
        ], $userId);
    }
    
    private function broadcastToRoom($roomId, $message, $excludeUserId = null) {
        // In a real implementation, this would send to WebSocket clients
        // For now, we'll store messages in a file-based queue
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
        
        // Keep only last 100 messages
        if (count($messages) > 100) {
            $messages = array_slice($messages, -100);
        }
        
        file_put_contents($messageFile, json_encode($messages));
    }
    
    private function getWebSocketClient() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>WebSocket Client</title>
        </head>
        <body>
            <h1>WebSocket Signaling Server</h1>
            <p>Server is running and ready to handle connections.</p>
        </body>
        </html>';
    }
}

// Start the server
$server = new WebSocketSignalingServer();
?>
