-- Nexoom Video Conferencing Platform Database
-- Created for proper meeting management like Zoom

-- Note: Database u765872199_nexoom_db already exists
-- This script will create tables in the existing database

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'broadcaster', 'viewer') DEFAULT 'viewer',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Meeting rooms table
CREATE TABLE meeting_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(50) UNIQUE NOT NULL,
    room_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    max_participants INT DEFAULT 100,
    meeting_type ENUM('public', 'private', 'scheduled') DEFAULT 'public',
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    password VARCHAR(50) DEFAULT NULL,
    recording_enabled BOOLEAN DEFAULT FALSE,
    screen_sharing_enabled BOOLEAN DEFAULT TRUE,
    chat_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Meeting participants table
CREATE TABLE meeting_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    participant_type ENUM('host', 'co-host', 'participant') DEFAULT 'participant',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    microphone_enabled BOOLEAN DEFAULT TRUE,
    video_enabled BOOLEAN DEFAULT TRUE,
    screen_sharing BOOLEAN DEFAULT FALSE,
    hand_raised BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (meeting_id) REFERENCES meeting_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meeting_user (meeting_id, user_id)
);

-- Meeting sessions table (for tracking active meetings)
CREATE TABLE meeting_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    participant_count INT DEFAULT 0,
    FOREIGN KEY (meeting_id) REFERENCES meeting_rooms(id) ON DELETE CASCADE
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'system', 'announcement') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meeting_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Meeting recordings table
CREATE TABLE meeting_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    recording_url VARCHAR(500) NOT NULL,
    recording_duration INT DEFAULT 0, -- in seconds
    file_size BIGINT DEFAULT 0, -- in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meeting_rooms(id) ON DELETE CASCADE
);

-- User sessions table (for login management)
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert seed data

-- Admin user
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Sample broadcasters
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('broadcaster1', 'broadcaster1@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Broadcaster', 'broadcaster'),
('broadcaster2', 'broadcaster2@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Broadcaster', 'broadcaster');

-- Sample viewers
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('viewer1', 'viewer1@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Viewer', 'viewer'),
('viewer2', 'viewer2@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Viewer', 'viewer'),
('viewer3', 'viewer3@nexoom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlie Viewer', 'viewer');

-- Sample meeting rooms
INSERT INTO meeting_rooms (room_id, room_name, description, created_by, meeting_type, max_participants, password) VALUES 
('nexoom_demo', 'Nexoom Demo Meeting', 'Demo meeting room for testing', 1, 'public', 50, NULL),
('nexoom_test', 'Nexoom Test Meeting', 'Test meeting room for development', 1, 'public', 25, NULL),
('nexoom_live', 'Nexoom Live Meeting', 'Live meeting room for production', 1, 'public', 100, NULL),
('private_meeting_001', 'Private Meeting Room 1', 'Private meeting with password protection', 2, 'private', 10, 'password123'),
('scheduled_meeting_001', 'Scheduled Meeting', 'Scheduled meeting for tomorrow', 3, 'scheduled', 30, NULL);

-- Sample meeting participants
INSERT INTO meeting_participants (meeting_id, user_id, participant_type, microphone_enabled, video_enabled) VALUES 
(1, 1, 'host', TRUE, TRUE),
(1, 4, 'participant', TRUE, TRUE),
(1, 5, 'participant', FALSE, TRUE),
(2, 2, 'host', TRUE, TRUE),
(2, 6, 'participant', TRUE, FALSE);

-- Sample chat messages
INSERT INTO chat_messages (meeting_id, user_id, message, message_type) VALUES 
(1, 1, 'Welcome to the Nexoom demo meeting!', 'system'),
(1, 4, 'Hello everyone!', 'text'),
(1, 5, 'Great to be here!', 'text'),
(2, 2, 'Test meeting started', 'system'),
(2, 6, 'Testing the system', 'text');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_meeting_rooms_room_id ON meeting_rooms(room_id);
CREATE INDEX idx_meeting_participants_meeting_id ON meeting_participants(meeting_id);
CREATE INDEX idx_meeting_participants_user_id ON meeting_participants(user_id);
CREATE INDEX idx_meeting_sessions_meeting_id ON meeting_sessions(meeting_id);
CREATE INDEX idx_meeting_sessions_token ON meeting_sessions(session_token);
CREATE INDEX idx_chat_messages_meeting_id ON chat_messages(meeting_id);
CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);

-- Create views for easier querying

-- Active meetings view
CREATE VIEW active_meetings AS
SELECT 
    mr.id,
    mr.room_id,
    mr.room_name,
    mr.description,
    u.full_name as created_by_name,
    mr.max_participants,
    mr.meeting_type,
    mr.start_time,
    mr.end_time,
    mr.password,
    mr.recording_enabled,
    mr.screen_sharing_enabled,
    mr.chat_enabled,
    COUNT(mp.id) as current_participants,
    mr.created_at
FROM meeting_rooms mr
LEFT JOIN users u ON mr.created_by = u.id
LEFT JOIN meeting_participants mp ON mr.id = mp.meeting_id AND mp.is_active = TRUE
WHERE mr.is_active = TRUE
GROUP BY mr.id;

-- Meeting participants view
CREATE VIEW meeting_participants_view AS
SELECT 
    mp.id,
    mp.meeting_id,
    mr.room_id,
    mr.room_name,
    mp.user_id,
    u.username,
    u.full_name,
    u.email,
    mp.participant_type,
    mp.joined_at,
    mp.left_at,
    mp.is_active,
    mp.microphone_enabled,
    mp.video_enabled,
    mp.screen_sharing,
    mp.hand_raised
FROM meeting_participants mp
JOIN meeting_rooms mr ON mp.meeting_id = mr.id
JOIN users u ON mp.user_id = u.id;

-- Recent chat messages view
CREATE VIEW recent_chat_messages AS
SELECT 
    cm.id,
    cm.meeting_id,
    mr.room_id,
    cm.user_id,
    u.username,
    u.full_name,
    cm.message,
    cm.message_type,
    cm.created_at
FROM chat_messages cm
JOIN meeting_rooms mr ON cm.meeting_id = mr.id
JOIN users u ON cm.user_id = u.id
ORDER BY cm.created_at DESC;

-- Stored procedures

-- Procedure to create a new meeting
DELIMITER //
CREATE PROCEDURE CreateMeeting(
    IN p_room_id VARCHAR(50),
    IN p_room_name VARCHAR(100),
    IN p_description TEXT,
    IN p_created_by INT,
    IN p_meeting_type ENUM('public', 'private', 'scheduled'),
    IN p_max_participants INT,
    IN p_password VARCHAR(50)
)
BEGIN
    DECLARE meeting_id INT;
    
    INSERT INTO meeting_rooms (room_id, room_name, description, created_by, meeting_type, max_participants, password)
    VALUES (p_room_id, p_room_name, p_description, p_created_by, p_meeting_type, p_max_participants, p_password);
    
    SET meeting_id = LAST_INSERT_ID();
    
    -- Add creator as host
    INSERT INTO meeting_participants (meeting_id, user_id, participant_type)
    VALUES (meeting_id, p_created_by, 'host');
    
    SELECT meeting_id as new_meeting_id;
END //
DELIMITER ;

-- Procedure to join a meeting
DELIMITER //
CREATE PROCEDURE JoinMeeting(
    IN p_room_id VARCHAR(50),
    IN p_user_id INT,
    IN p_participant_type ENUM('host', 'co-host', 'participant')
)
BEGIN
    DECLARE meeting_id INT;
    DECLARE participant_count INT;
    
    -- Get meeting ID
    SELECT id INTO meeting_id FROM meeting_rooms WHERE room_id = p_room_id AND is_active = TRUE;
    
    IF meeting_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Meeting not found or inactive';
    END IF;
    
    -- Check if user is already in meeting
    IF EXISTS (SELECT 1 FROM meeting_participants WHERE meeting_id = meeting_id AND user_id = p_user_id AND is_active = TRUE) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User already in meeting';
    END IF;
    
    -- Add participant
    INSERT INTO meeting_participants (meeting_id, user_id, participant_type)
    VALUES (meeting_id, p_user_id, p_participant_type);
    
    -- Update participant count
    SELECT COUNT(*) INTO participant_count FROM meeting_participants WHERE meeting_id = meeting_id AND is_active = TRUE;
    UPDATE meeting_sessions SET participant_count = participant_count WHERE meeting_id = meeting_id AND is_active = TRUE;
    
    SELECT meeting_id, participant_count;
END //
DELIMITER ;

-- Procedure to leave a meeting
DELIMITER //
CREATE PROCEDURE LeaveMeeting(
    IN p_room_id VARCHAR(50),
    IN p_user_id INT
)
BEGIN
    DECLARE meeting_id INT;
    DECLARE participant_count INT;
    
    -- Get meeting ID
    SELECT id INTO meeting_id FROM meeting_rooms WHERE room_id = p_room_id AND is_active = TRUE;
    
    IF meeting_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Meeting not found or inactive';
    END IF;
    
    -- Mark participant as inactive
    UPDATE meeting_participants 
    SET is_active = FALSE, left_at = CURRENT_TIMESTAMP 
    WHERE meeting_id = meeting_id AND user_id = p_user_id AND is_active = TRUE;
    
    -- Update participant count
    SELECT COUNT(*) INTO participant_count FROM meeting_participants WHERE meeting_id = meeting_id AND is_active = TRUE;
    UPDATE meeting_sessions SET participant_count = participant_count WHERE meeting_id = meeting_id AND is_active = TRUE;
    
    SELECT meeting_id, participant_count;
END //
DELIMITER ;

-- Procedure to get meeting details
DELIMITER //
CREATE PROCEDURE GetMeetingDetails(
    IN p_room_id VARCHAR(50)
)
BEGIN
    SELECT 
        mr.id,
        mr.room_id,
        mr.room_name,
        mr.description,
        u.full_name as created_by_name,
        mr.max_participants,
        mr.meeting_type,
        mr.start_time,
        mr.end_time,
        mr.password,
        mr.recording_enabled,
        mr.screen_sharing_enabled,
        mr.chat_enabled,
        COUNT(mp.id) as current_participants,
        mr.created_at
    FROM meeting_rooms mr
    LEFT JOIN users u ON mr.created_by = u.id
    LEFT JOIN meeting_participants mp ON mr.id = mp.meeting_id AND mp.is_active = TRUE
    WHERE mr.room_id = p_room_id AND mr.is_active = TRUE
    GROUP BY mr.id;
END //
DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- Note: Permissions are managed by your hosting provider
-- GRANT ALL PRIVILEGES ON u765872199_nexoom_db.* TO 'u765872199_patrickssajeev'@'localhost';
-- FLUSH PRIVILEGES;

-- End of database setup
