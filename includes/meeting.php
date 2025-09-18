<?php
// Meeting management system for Nexoom Video Conferencing Platform

require_once 'config/database.php';

class Meeting {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Create a new meeting
    public function createMeeting($room_id, $room_name, $description, $created_by, $meeting_type = 'public', $max_participants = 100, $password = null) {
        try {
            // Check if room ID already exists
            if ($this->roomExists($room_id)) {
                return ['success' => false, 'message' => 'Room ID already exists'];
            }
            
            // Create meeting
            $sql = "INSERT INTO meeting_rooms (room_id, room_name, description, created_by, meeting_type, max_participants, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$room_id, $room_name, $description, $created_by, $meeting_type, $max_participants, $password]);
            
            if ($result) {
                $meeting_id = $this->db->lastInsertId();
                
                // Add creator as host
                $this->addParticipant($meeting_id, $created_by, 'host');
                
                return ['success' => true, 'message' => 'Meeting created successfully', 'meeting_id' => $meeting_id];
            } else {
                return ['success' => false, 'message' => 'Failed to create meeting'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Get meeting details
    public function getMeeting($room_id) {
        try {
            $sql = "SELECT * FROM meeting_rooms WHERE room_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$room_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Get all active meetings
    public function getActiveMeetings() {
        try {
            $sql = "SELECT * FROM active_meetings ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Join a meeting
    public function joinMeeting($room_id, $user_id, $participant_type = 'participant') {
        try {
            // Get meeting details
            $meeting = $this->getMeeting($room_id);
            if (!$meeting) {
                return ['success' => false, 'message' => 'Meeting not found or inactive'];
            }
            
            // Check if user is already in meeting
            if ($this->isUserInMeeting($meeting['id'], $user_id)) {
                return ['success' => true, 'message' => 'User already in meeting'];
            }
            
            // Check participant limit
            $current_participants = $this->getParticipantCount($meeting['id']);
            if ($current_participants >= $meeting['max_participants']) {
                return ['success' => false, 'message' => 'Meeting is full'];
            }
            
            // Add participant
            $result = $this->addParticipant($meeting['id'], $user_id, $participant_type);
            
            if ($result['success']) {
                // Start meeting session if not already started
                $this->startMeetingSession($meeting['id']);
                
                return ['success' => true, 'message' => 'Joined meeting successfully', 'meeting' => $meeting];
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Leave a meeting
    public function leaveMeeting($room_id, $user_id) {
        try {
            $meeting = $this->getMeeting($room_id);
            if (!$meeting) {
                return ['success' => false, 'message' => 'Meeting not found'];
            }
            
            // Mark participant as inactive
            $sql = "UPDATE meeting_participants SET is_active = FALSE, left_at = CURRENT_TIMESTAMP WHERE meeting_id = ? AND user_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$meeting['id'], $user_id]);
            
            if ($result) {
                // Update participant count
                $this->updateParticipantCount($meeting['id']);
                
                return ['success' => true, 'message' => 'Left meeting successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to leave meeting'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Get meeting participants
    public function getMeetingParticipants($room_id) {
        try {
            $sql = "SELECT * FROM meeting_participants_view WHERE room_id = ? AND is_active = TRUE ORDER BY joined_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$room_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Add participant to meeting
    private function addParticipant($meeting_id, $user_id, $participant_type = 'participant') {
        try {
            $sql = "INSERT INTO meeting_participants (meeting_id, user_id, participant_type) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$meeting_id, $user_id, $participant_type]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Participant added successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to add participant'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Check if room exists
    private function roomExists($room_id) {
        $sql = "SELECT COUNT(*) FROM meeting_rooms WHERE room_id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$room_id]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Check if user is in meeting
    private function isUserInMeeting($meeting_id, $user_id) {
        $sql = "SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = ? AND user_id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$meeting_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Get participant count
    private function getParticipantCount($meeting_id) {
        $sql = "SELECT COUNT(*) FROM meeting_participants WHERE meeting_id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$meeting_id]);
        return $stmt->fetchColumn();
    }
    
    // Update participant count
    private function updateParticipantCount($meeting_id) {
        $count = $this->getParticipantCount($meeting_id);
        $sql = "UPDATE meeting_sessions SET participant_count = ? WHERE meeting_id = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$count, $meeting_id]);
    }
    
    // Start meeting session
    private function startMeetingSession($meeting_id) {
        try {
            // Check if session already exists
            $sql = "SELECT COUNT(*) FROM meeting_sessions WHERE meeting_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$meeting_id]);
            
            if ($stmt->fetchColumn() == 0) {
                // Create new session
                $session_token = bin2hex(random_bytes(32));
                $sql = "INSERT INTO meeting_sessions (meeting_id, session_token) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$meeting_id, $session_token]);
            }
        } catch (PDOException $e) {
            // Handle error silently
        }
    }
    
    // End meeting session
    public function endMeetingSession($room_id) {
        try {
            $meeting = $this->getMeeting($room_id);
            if (!$meeting) {
                return ['success' => false, 'message' => 'Meeting not found'];
            }
            
            // Mark session as inactive
            $sql = "UPDATE meeting_sessions SET is_active = FALSE, ended_at = CURRENT_TIMESTAMP WHERE meeting_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$meeting['id']]);
            
            // Mark all participants as inactive
            $sql = "UPDATE meeting_participants SET is_active = FALSE, left_at = CURRENT_TIMESTAMP WHERE meeting_id = ? AND is_active = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$meeting['id']]);
            
            return ['success' => true, 'message' => 'Meeting session ended successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Get meeting statistics
    public function getMeetingStats($room_id) {
        try {
            $meeting = $this->getMeeting($room_id);
            if (!$meeting) {
                return null;
            }
            
            $participants = $this->getMeetingParticipants($room_id);
            $participant_count = count($participants);
            
            return [
                'meeting' => $meeting,
                'participants' => $participants,
                'participant_count' => $participant_count,
                'max_participants' => $meeting['max_participants'],
                'is_full' => $participant_count >= $meeting['max_participants']
            ];
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Global meeting instance
$meeting = new Meeting();
?>
