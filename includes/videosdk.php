<?php
// VideoSDK Integration for Nexoom Video Conferencing Platform

class VideoSDK {
    private $apiKey;
    private $secretKey;
    private $baseUrl = 'https://api.videosdk.live/v2';
    
    public function __construct($apiKey, $secretKey) {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }
    
    /**
     * Generate JWT token for VideoSDK authentication
     */
    public function generateToken($permissions = ['allow_join']) {
        $payload = [
            'apikey' => $this->apiKey,
            'permissions' => $permissions,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        // For production, you should use a proper JWT library
        // This is a simplified version for demonstration
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Create a new meeting
     */
    public function createMeeting($meetingId = null, $customRoomId = null) {
        $url = $this->baseUrl . '/rooms';
        
        $data = [];
        if ($meetingId) {
            $data['customRoomId'] = $meetingId;
        }
        if ($customRoomId) {
            $data['customRoomId'] = $customRoomId;
        }
        
        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['roomId'])) {
            return [
                'success' => true,
                'meetingId' => $response['roomId'],
                'data' => $response
            ];
        }
        
        // Provide more detailed error information
        $errorMessage = 'Failed to create meeting';
        if (isset($response['error'])) {
            if (is_array($response['error'])) {
                $errorMessage = $response['error']['message'] ?? $errorMessage;
            } else {
                $errorMessage = $response['error'];
            }
        }
        
        return [
            'success' => false,
            'message' => $errorMessage,
            'error' => $response,
            'http_code' => $response['http_code'] ?? null
        ];
    }
    
    /**
     * Validate a meeting
     */
    public function validateMeeting($meetingId) {
        $url = $this->baseUrl . '/rooms/validate/' . $meetingId;
        
        $response = $this->makeRequest('GET', $url);
        
        if ($response && isset($response['roomId'])) {
            return [
                'success' => true,
                'meetingId' => $response['roomId'],
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Invalid meeting ID',
            'error' => $response
        ];
    }
    
    /**
     * Get meeting details
     */
    public function getMeeting($meetingId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId;
        
        $response = $this->makeRequest('GET', $url);
        
        if ($response && isset($response['roomId'])) {
            return [
                'success' => true,
                'meetingId' => $response['roomId'],
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Meeting not found',
            'error' => $response
        ];
    }
    
    /**
     * End a meeting
     */
    public function endMeeting($meetingId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/end';
        
        $response = $this->makeRequest('POST', $url);
        
        if ($response) {
            return [
                'success' => true,
                'message' => 'Meeting ended successfully',
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to end meeting',
            'error' => $response
        ];
    }
    
    /**
     * Get meeting participants
     */
    public function getParticipants($meetingId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/participants';
        
        $response = $this->makeRequest('GET', $url);
        
        if ($response && is_array($response)) {
            return [
                'success' => true,
                'participants' => $response,
                'count' => count($response)
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to get participants',
            'error' => $response
        ];
    }
    
    /**
     * Remove a participant from meeting
     */
    public function removeParticipant($meetingId, $participantId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/participants/' . $participantId;
        
        $response = $this->makeRequest('DELETE', $url);
        
        if ($response) {
            return [
                'success' => true,
                'message' => 'Participant removed successfully',
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to remove participant',
            'error' => $response
        ];
    }
    
    /**
     * Start recording
     */
    public function startRecording($meetingId, $webhookUrl = null) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/recordings/start';
        
        $data = [];
        if ($webhookUrl) {
            $data['webhookUrl'] = $webhookUrl;
        }
        
        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['recordingId'])) {
            return [
                'success' => true,
                'recordingId' => $response['recordingId'],
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to start recording',
            'error' => $response
        ];
    }
    
    /**
     * Stop recording
     */
    public function stopRecording($meetingId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/recordings/stop';
        
        $response = $this->makeRequest('POST', $url);
        
        if ($response) {
            return [
                'success' => true,
                'message' => 'Recording stopped successfully',
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to stop recording',
            'error' => $response
        ];
    }
    
    /**
     * Start live streaming
     */
    public function startLiveStream($meetingId, $rtmpUrls) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/livestreams/start';
        
        $data = [
            'rtmpUrls' => $rtmpUrls
        ];
        
        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['streamId'])) {
            return [
                'success' => true,
                'streamId' => $response['streamId'],
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to start live stream',
            'error' => $response
        ];
    }
    
    /**
     * Stop live streaming
     */
    public function stopLiveStream($meetingId, $streamId) {
        $url = $this->baseUrl . '/rooms/' . $meetingId . '/livestreams/' . $streamId . '/stop';
        
        $response = $this->makeRequest('POST', $url);
        
        if ($response) {
            return [
                'success' => true,
                'message' => 'Live stream stopped successfully',
                'data' => $response
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to stop live stream',
            'error' => $response
        ];
    }
    
    /**
     * Make HTTP request to VideoSDK API
     */
    private function makeRequest($method, $url, $data = null) {
        // Generate JWT token for authentication
        $token = $this->generateToken();
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log the request for debugging
        error_log("VideoSDK API Request: $method $url");
        error_log("VideoSDK API Response Code: $httpCode");
        error_log("VideoSDK API Response: " . $response);
        
        if ($error) {
            error_log("VideoSDK API cURL Error: " . $error);
            return [
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $decodedResponse;
        } else {
            error_log("VideoSDK API Error Response: " . json_encode($decodedResponse));
            return [
                'error' => $decodedResponse,
                'http_code' => $httpCode,
                'raw_response' => $response
            ];
        }
    }
    
    /**
     * Get VideoSDK configuration for frontend
     */
    public function getFrontendConfig($meetingId, $participantName, $participantId = null) {
        // Use the provided token directly for better compatibility
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiIwZmM4ZTFhNS1jMDczLTQwN2MtOWJmNC0xNTM0NDI0MzM0MzIiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0ODI0MSwiZXhwIjoxNzg5ODg0MjQxfQ.TqA0BOWZPwbg4MPr1Ox5HgLX1KXSNTPAU8jSePLmEM4';
        
        return [
            'apiKey' => $this->apiKey,
            'token' => $token,
            'meetingId' => $meetingId,
            'participantName' => $participantName,
            'participantId' => $participantId ?: uniqid('participant_'),
            'region' => 'sg001', // Singapore region
            'micEnabled' => true,
            'webcamEnabled' => true,
            'screenShareEnabled' => true,
            'chatEnabled' => true,
            'recordingEnabled' => true,
            'liveStreamEnabled' => true,
            'whiteboardEnabled' => true,
            'pollEnabled' => true,
            'raiseHandEnabled' => true,
            'participantCanToggleSelfWebcam' => true,
            'participantCanToggleSelfMic' => true,
            'participantCanLeave' => true,
            'participantCanEndMeeting' => false,
            'participantCanToggleRecording' => false,
            'participantCanToggleLivestream' => false,
            'participantCanToggleOtherWebcam' => false,
            'participantCanToggleOtherMic' => false,
            'participantCanToggleRealtimeTranscription' => false,
            'realtimeTranscriptionEnabled' => false,
            'realtimeTranscriptionVisible' => false,
            'recordingEnabled' => true,
            'autoStartRecording' => false,
            'brandingEnabled' => true,
            'brandName' => 'Nexoom',
            'poweredBy' => false,
            'liveStreamEnabled' => true,
            'autoStartLiveStream' => false,
            'askJoin' => false,
            'joinScreenEnabled' => true,
            'notificationSoundEnabled' => true,
            'canPin' => true,
            'canRemoveOtherParticipant' => false,
            'canDrawOnWhiteboard' => true,
            'canToggleWhiteboard' => true,
            'maxResolution' => 'hd',
            'animationsEnabled' => true,
            'topbarEnabled' => true,
            'notificationAlertsEnabled' => true,
            'debug' => true,
            'layoutType' => 'GRID',
            'layoutPriority' => 'SPEAKER',
            'meetingLayoutTopic' => 'MEETING_LAYOUT',
            'isRecorder' => false,
            'hideLocalParticipant' => false,
            'alwaysShowOverlay' => false,
            'reduceEdgeSpacing' => false,
            'joinWithoutUserInteraction' => false,
            'canChangeLayout' => true,
            'preferredProtocol' => 'UDP_ONLY'
        ];
    }
}

// Global VideoSDK instance
$videoSDK = new VideoSDK('0fc8e1a5-c073-407c-9bf4-153442433432', '208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e');

// Helper functions
function getVideoSDK() {
    global $videoSDK;
    return $videoSDK;
}

function generateVideoSDKToken() {
    global $videoSDK;
    return $videoSDK->generateToken();
}

function createVideoSDKMeeting($meetingId = null) {
    global $videoSDK;
    return $videoSDK->createMeeting($meetingId);
}

function validateVideoSDKMeeting($meetingId) {
    global $videoSDK;
    return $videoSDK->validateMeeting($meetingId);
}

function getVideoSDKMeeting($meetingId) {
    global $videoSDK;
    return $videoSDK->getMeeting($meetingId);
}

function endVideoSDKMeeting($meetingId) {
    global $videoSDK;
    return $videoSDK->endMeeting($meetingId);
}

function getVideoSDKParticipants($meetingId) {
    global $videoSDK;
    return $videoSDK->getParticipants($meetingId);
}

function getVideoSDKConfig($meetingId, $participantName, $participantId = null) {
    global $videoSDK;
    return $videoSDK->getFrontendConfig($meetingId, $participantName, $participantId);
}
?>
