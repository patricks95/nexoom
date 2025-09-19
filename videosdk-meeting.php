<?php
require_once 'includes/auth.php';
require_once 'includes/meeting.php';
require_once 'includes/videosdk.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$meetingId = isset($_GET['room']) ? $_GET['room'] : '';
$userRole = $user['role'];

// If no meeting ID provided, redirect to index
if (empty($meetingId)) {
    header('Location: home.php');
    exit();
}

// Initialize meeting system
$meeting = new Meeting();
$error_message = '';
$success_message = '';

// Get or create meeting
$meetingData = $meeting->getMeeting($meetingId);
$videoSDKMeeting = null;

if (!$meetingData) {
    // Meeting doesn't exist, create it if user is broadcaster or admin
    if ($userRole === 'broadcaster' || $userRole === 'admin') {
        // First create VideoSDK meeting
        $videoSDKResult = createVideoSDKMeeting($meetingId);
        
        if ($videoSDKResult['success']) {
            // Then create local meeting record
            $result = $meeting->createMeeting(
                $meetingId,
                'Meeting ' . $meetingId,
                'Meeting created by ' . $user['full_name'],
                $user['id'],
                'public',
                100,
                null
            );
            
            if ($result['success']) {
                $meetingData = $meeting->getMeeting($meetingId);
                $videoSDKMeeting = $videoSDKResult;
                $success_message = 'Meeting created successfully!';
            } else {
                $error_message = $result['message'];
            }
        } else {
            $error_message = 'Failed to create VideoSDK meeting: ' . $videoSDKResult['message'];
        }
    } else {
        $error_message = 'Meeting not found. Only broadcasters and admins can create new meetings.';
    }
} else {
    // Validate existing meeting with VideoSDK
    $videoSDKResult = validateVideoSDKMeeting($meetingId);
    if ($videoSDKResult['success']) {
        $videoSDKMeeting = $videoSDKResult;
    } else {
        $error_message = 'VideoSDK meeting validation failed: ' . $videoSDKResult['message'];
    }
}

// Join meeting if valid
if ($meetingData && empty($error_message)) {
    // Check if user is already in meeting
    $participants = $meeting->getMeetingParticipants($meetingId);
    $userAlreadyInMeeting = false;
    
    foreach ($participants as $participant) {
        if ($participant['user_id'] == $user['id'] && $participant['is_active']) {
            $userAlreadyInMeeting = true;
            break;
        }
    }
    
    if (!$userAlreadyInMeeting) {
        $joinResult = $meeting->joinMeeting($meetingId, $user['id'], $userRole === 'broadcaster' ? 'host' : 'participant');
        if (!$joinResult['success']) {
            $error_message = $joinResult['message'];
        } else {
            $success_message = 'Successfully joined meeting!';
        }
    } else {
        $success_message = 'Already in meeting!';
    }
}

// Get VideoSDK configuration
$videoSDKConfig = getVideoSDKConfig($meetingId, $user['full_name'], $user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Meeting - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://sdk.videosdk.live/js-sdk/0.0.68/videosdk.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            overflow: hidden;
        }
        
        .meeting-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            background: #000;
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px;
            padding: 10px;
            height: calc(100vh - 80px);
        }
        
        .video-item {
            position: relative;
            background: #1a1a1a;
            border-radius: 15px;
            overflow: hidden;
            min-height: 200px;
            border: 2px solid #333;
            transition: all 0.3s ease;
        }
        
        .video-item:hover {
            border-color: #3b82f6;
            transform: scale(1.02);
        }
        
        .video-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .video-label {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        
        .controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            padding: 20px 40px;
            border-radius: 50px;
            display: flex;
            gap: 20px;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .control-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        
        .control-btn:hover::before {
            transform: translateX(100%);
        }
        
        .control-btn:hover {
            transform: scale(1.1);
        }
        
        .control-btn.mic {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .control-btn.mic.muted {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .control-btn.video {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .control-btn.video.muted {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .control-btn.screen {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }
        
        .control-btn.screen.active {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .control-btn.chat {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .control-btn.record {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .control-btn.record.active {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .control-btn.hangup {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .status-bar {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .participant-count {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.95);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            z-index: 1001;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .success {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            z-index: 1000;
            backdrop-filter: blur(20px);
        }
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 20px;
            z-index: 999;
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .chat-panel {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 350px;
            height: 500px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 20px;
            display: none;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 600;
        }
        
        .chat-messages {
            height: 350px;
            overflow-y: auto;
            padding: 20px;
            color: white;
        }
        
        .chat-message {
            margin-bottom: 15px;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }
        
        .chat-message .sender {
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        
        .chat-input {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
        }
        
        .chat-input input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
        }
        
        .chat-input input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .recording-indicator {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            z-index: 1000;
            backdrop-filter: blur(20px);
            display: none;
        }
        
        .recording-indicator.active {
            display: block;
        }
        
        .recording-indicator .pulse {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            margin-right: 10px;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="meeting-container">
        <?php if (!empty($error_message)): ?>
        <div class="error">
            <h3 style="font-size: 24px; margin-bottom: 20px;">❌ Error</h3>
            <p style="font-size: 16px; margin-bottom: 30px;"><?php echo htmlspecialchars($error_message); ?></p>
            <button onclick="window.location.href='home.php'" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 15px 30px; border-radius: 25px; cursor: pointer; font-size: 16px; font-weight: 600;">
                Go Back
            </button>
        </div>
        <?php else: ?>
        <div class="status-bar">
            <i class="fas fa-circle text-green-500"></i> 
            <span style="margin-left: 10px; font-weight: 600;">Meeting: <?php echo $meetingId; ?></span>
        </div>
        
        <div class="participant-count">
            <i class="fas fa-users"></i> 
            <span style="margin-left: 10px; font-weight: 600;" id="participant-count">1</span>
        </div>
        
        <div class="recording-indicator" id="recording-indicator">
            <span class="pulse"></span>
            Recording in progress...
        </div>
        
        <?php if (!empty($success_message)): ?>
        <div class="success">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <div>Starting video meeting...</div>
        </div>
        
        <div class="video-grid" id="video-grid">
            <!-- Videos will be added here dynamically -->
        </div>
        
        <div class="controls">
            <button class="control-btn mic" id="mic-btn" onclick="toggleMic()">
                <i class="fas fa-microphone"></i>
            </button>
            <button class="control-btn video" id="video-btn" onclick="toggleVideo()">
                <i class="fas fa-video"></i>
            </button>
            <button class="control-btn screen" id="screen-btn" onclick="toggleScreenShare()">
                <i class="fas fa-desktop"></i>
            </button>
            <button class="control-btn chat" id="chat-btn" onclick="toggleChat()">
                <i class="fas fa-comments"></i>
            </button>
            <?php if ($userRole === 'broadcaster' || $userRole === 'admin'): ?>
            <button class="control-btn record" id="record-btn" onclick="toggleRecording()">
                <i class="fas fa-circle"></i>
            </button>
            <?php endif; ?>
            <button class="control-btn hangup" id="hangup-btn" onclick="hangup()">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
        
        <div class="chat-panel" id="chat-panel">
            <div class="chat-header">
                <i class="fas fa-comments"></i> Chat
            </div>
            <div class="chat-messages" id="chat-messages"></div>
            <div class="chat-input">
                <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleChatKeyPress(event)">
            </div>
        </div>
        
        <script>
            let meeting = null;
            let isMicOn = true;
            let isVideoOn = true;
            let isScreenSharing = false;
            let isChatOpen = false;
            let isRecording = false;
            let participantCount = 1;
            
            // VideoSDK configuration from PHP
            const videoSDKConfig = <?php echo json_encode($videoSDKConfig); ?>;
            
            // Initialize VideoSDK meeting
            async function initVideoSDKMeeting() {
                try {
                    document.getElementById('loading').style.display = 'block';
                    
                    // Configure VideoSDK
                    VideoSDK.config(videoSDKConfig.apiKey);
                    
                    // Create meeting with proper configuration
                    meeting = VideoSDK.initMeeting({
                        meetingId: videoSDKConfig.meetingId,
                        name: videoSDKConfig.participantName,
                        micEnabled: videoSDKConfig.micEnabled,
                        webcamEnabled: videoSDKConfig.webcamEnabled,
                        participantId: videoSDKConfig.participantId,
                        region: videoSDKConfig.region,
                        debug: videoSDKConfig.debug,
                        // Additional configurations
                        enableScreenShare: videoSDKConfig.screenShareEnabled,
                        enableChat: videoSDKConfig.chatEnabled,
                        enablePoll: videoSDKConfig.pollEnabled,
                        enableWhiteboard: videoSDKConfig.whiteboardEnabled,
                        enableRecording: videoSDKConfig.recordingEnabled,
                        enableLiveStream: videoSDKConfig.liveStreamEnabled,
                        // Permissions
                        participantCanToggleSelfWebcam: videoSDKConfig.participantCanToggleSelfWebcam,
                        participantCanToggleSelfMic: videoSDKConfig.participantCanToggleSelfMic,
                        participantCanLeave: videoSDKConfig.participantCanLeave,
                        participantCanEndMeeting: videoSDKConfig.participantCanEndMeeting,
                        // Layout settings
                        layoutType: videoSDKConfig.layoutType,
                        layoutPriority: videoSDKConfig.layoutPriority,
                        maxResolution: videoSDKConfig.maxResolution,
                        // Branding
                        brandingEnabled: videoSDKConfig.brandingEnabled,
                        brandName: videoSDKConfig.brandName,
                        poweredBy: videoSDKConfig.poweredBy
                    });
                    
                    // Set up event listeners
                    setupMeetingEvents();
                    
                    // Join meeting with timeout
                    setTimeout(() => {
                        meeting.join();
                    }, 1000);
                    
                    console.log('VideoSDK meeting initialized successfully');
                    
                } catch (error) {
                    console.error('Error initializing VideoSDK meeting:', error);
                    document.getElementById('loading').innerHTML = '<div class="spinner"></div><div>Error starting meeting. Please try again.</div>';
                }
            }
            
            // Set up meeting event listeners
            function setupMeetingEvents() {
                // Meeting joined
                meeting.on("meeting-joined", () => {
                    document.getElementById('loading').style.display = 'none';
                    console.log('Successfully joined meeting');
                    // Add local video to grid
                    addLocalVideo();
                });
                
                // Participant joined
                meeting.on("participant-joined", (participant) => {
                    console.log('Participant joined:', participant);
                    updateParticipantCount();
                    addParticipantVideo(participant);
                });
                
                // Participant left
                meeting.on("participant-left", (participant) => {
                    console.log('Participant left:', participant);
                    updateParticipantCount();
                    removeParticipantVideo(participant.id);
                });
                
                // Meeting left
                meeting.on("meeting-left", () => {
                    console.log('Left meeting');
                    window.location.href = 'home.php';
                });
                
                // Error handling
                meeting.on("error", (error) => {
                    console.error('Meeting error:', error);
                    document.getElementById('loading').innerHTML = '<div class="spinner"></div><div>Error in meeting. Please try again.</div>';
                });
                
                // Recording events
                meeting.on("recording-started", () => {
                    console.log('Recording started');
                    isRecording = true;
                    updateRecordingUI();
                });
                
                meeting.on("recording-stopped", () => {
                    console.log('Recording stopped');
                    isRecording = false;
                    updateRecordingUI();
                });
                
                // Add timeout fallback
                setTimeout(() => {
                    if (document.getElementById('loading').style.display !== 'none') {
                        console.log('Meeting taking too long to start, trying alternative approach...');
                        // Try to start meeting again
                        try {
                            meeting.join();
                        } catch (e) {
                            console.error('Failed to join meeting:', e);
                            document.getElementById('loading').innerHTML = '<div class="spinner"></div><div>Unable to start meeting. Please refresh and try again.</div>';
                        }
                    }
                }, 10000); // 10 second timeout
            }
            
            // Add local video to grid
            function addLocalVideo() {
                const videoGrid = document.getElementById('video-grid');
                const videoItem = document.createElement('div');
                videoItem.className = 'video-item local';
                videoItem.id = 'local-video';
                
                const video = document.createElement('video');
                video.autoplay = true;
                video.playsInline = true;
                video.muted = true;
                video.id = 'local-video-element';
                
                const label = document.createElement('div');
                label.className = 'video-label';
                label.textContent = 'You';
                
                videoItem.appendChild(video);
                videoItem.appendChild(label);
                videoGrid.appendChild(videoItem);
                
                // Get user media for local video
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then(stream => {
                        video.srcObject = stream;
                    })
                    .catch(error => {
                        console.error('Error accessing camera:', error);
                    });
            }
            
            // Add participant video
            function addParticipantVideo(participant) {
                const videoGrid = document.getElementById('video-grid');
                const videoItem = document.createElement('div');
                videoItem.className = 'video-item';
                videoItem.id = `participant-${participant.id}`;
                
                const video = document.createElement('video');
                video.autoplay = true;
                video.playsInline = true;
                video.muted = true;
                
                const label = document.createElement('div');
                label.className = 'video-label';
                label.textContent = participant.displayName || 'Participant';
                
                videoItem.appendChild(video);
                videoItem.appendChild(label);
                videoGrid.appendChild(videoItem);
                
                // Set up video stream
                participant.on("stream-enabled", (stream) => {
                    video.srcObject = stream;
                });
            }
            
            // Remove participant video
            function removeParticipantVideo(participantId) {
                const videoItem = document.getElementById(`participant-${participantId}`);
                if (videoItem) {
                    videoItem.remove();
                }
            }
            
            // Update participant count
            function updateParticipantCount() {
                if (meeting) {
                    const participants = meeting.participants;
                    participantCount = Object.keys(participants).length;
                    document.getElementById('participant-count').textContent = participantCount;
                }
            }
            
            // Toggle microphone
            function toggleMic() {
                if (meeting) {
                    meeting.toggleMic();
                    isMicOn = !isMicOn;
                    const micBtn = document.getElementById('mic-btn');
                    micBtn.classList.toggle('muted', !isMicOn);
                    micBtn.innerHTML = isMicOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
                }
            }
            
            // Toggle video
            function toggleVideo() {
                if (meeting) {
                    meeting.toggleWebcam();
                    isVideoOn = !isVideoOn;
                    const videoBtn = document.getElementById('video-btn');
                    videoBtn.classList.toggle('muted', !isVideoOn);
                    videoBtn.innerHTML = isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
                }
            }
            
            // Toggle screen sharing
            function toggleScreenShare() {
                if (!isScreenSharing) {
                    startScreenShare();
                } else {
                    stopScreenShare();
                }
            }
            
            // Start screen sharing
            function startScreenShare() {
                if (meeting) {
                    meeting.enableScreenShare();
                    isScreenSharing = true;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.classList.add('active');
                    screenBtn.innerHTML = '<i class="fas fa-stop"></i>';
                }
            }
            
            // Stop screen sharing
            function stopScreenShare() {
                if (meeting) {
                    meeting.disableScreenShare();
                    isScreenSharing = false;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.classList.remove('active');
                    screenBtn.innerHTML = '<i class="fas fa-desktop"></i>';
                }
            }
            
            // Toggle recording
            function toggleRecording() {
                if (!isRecording) {
                    startRecording();
                } else {
                    stopRecording();
                }
            }
            
            // Start recording
            function startRecording() {
                if (meeting) {
                    meeting.startRecording();
                    isRecording = true;
                    updateRecordingUI();
                }
            }
            
            // Stop recording
            function stopRecording() {
                if (meeting) {
                    meeting.stopRecording();
                    isRecording = false;
                    updateRecordingUI();
                }
            }
            
            // Update recording UI
            function updateRecordingUI() {
                const recordBtn = document.getElementById('record-btn');
                const recordingIndicator = document.getElementById('recording-indicator');
                
                if (isRecording) {
                    recordBtn.classList.add('active');
                    recordBtn.innerHTML = '<i class="fas fa-stop"></i>';
                    recordingIndicator.classList.add('active');
                } else {
                    recordBtn.classList.remove('active');
                    recordBtn.innerHTML = '<i class="fas fa-circle"></i>';
                    recordingIndicator.classList.remove('active');
                }
            }
            
            // Toggle chat
            function toggleChat() {
                isChatOpen = !isChatOpen;
                const chatPanel = document.getElementById('chat-panel');
                chatPanel.style.display = isChatOpen ? 'block' : 'none';
            }
            
            // Handle chat key press
            function handleChatKeyPress(event) {
                if (event.key === 'Enter') {
                    const input = document.getElementById('chat-input');
                    const message = input.value.trim();
                    if (message) {
                        addChatMessage(videoSDKConfig.participantName, message);
                        input.value = '';
                    }
                }
            }
            
            // Add chat message
            function addChatMessage(sender, message) {
                const chatMessages = document.getElementById('chat-messages');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chat-message';
                messageDiv.innerHTML = `<div class="sender">${sender}</div><div>${message}</div>`;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Hangup
            function hangup() {
                if (meeting) {
                    meeting.leave();
                }
                window.location.href = 'home.php';
            }
            
            // Initialize when page loads
            window.addEventListener('load', initVideoSDKMeeting);
            
            // Handle page unload
            window.addEventListener('beforeunload', function() {
                if (meeting) {
                    meeting.leave();
                }
            });
            
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
