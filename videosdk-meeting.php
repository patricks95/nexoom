<?php
require_once 'includes/auth.php';
require_once 'includes/meeting.php';

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
    header('Location: index.php');
    exit();
}

// Initialize meeting system
$meeting = new Meeting();
$error_message = '';
$success_message = '';

// Get or create meeting
$meetingData = $meeting->getMeeting($meetingId);

if (!$meetingData) {
    // Meeting doesn't exist, create it if user is broadcaster or admin
    if ($userRole === 'broadcaster' || $userRole === 'admin') {
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
            $success_message = 'Meeting created successfully!';
        } else {
            $error_message = $result['message'];
        }
    } else {
        $error_message = 'Meeting not found. Only broadcasters and admins can create new meetings.';
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

// VideoSDK Configuration
$videosdk_api_key = '0fc8e1a5-c073-407c-9bf4-153442433432';
$videosdk_secret_key = '208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Conference - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://sdk.videosdk.live/js-sdk/0.0.68/videosdk.js"></script>
    <style>
        body { margin: 0; padding: 0; background: #1a1a1a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .video-container { position: relative; width: 100vw; height: 100vh; background: #000; }
        .status-bar { position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .participant-count { position: fixed; top: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .error { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); color: white; padding: 30px; border-radius: 10px; text-align: center; z-index: 1001; }
        .success { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,255,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .loading { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 18px; z-index: 999; }
        .meeting-container { width: 100%; height: 100%; }
        .controls { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.8); padding: 15px 30px; border-radius: 50px; display: flex; gap: 15px; z-index: 1000; }
        .control-btn { width: 50px; height: 50px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.3s; }
        .control-btn.mic { background: #10b981; color: white; }
        .control-btn.mic.muted { background: #ef4444; }
        .control-btn.video { background: #3b82f6; color: white; }
        .control-btn.video.muted { background: #ef4444; }
        .control-btn.screen { background: #8b5cf6; color: white; }
        .control-btn.hangup { background: #ef4444; color: white; }
        .control-btn:hover { transform: scale(1.1); }
        .chat-panel { position: fixed; right: 20px; top: 50%; transform: translateY(-50%); width: 300px; height: 400px; background: rgba(0,0,0,0.8); border-radius: 10px; display: none; z-index: 1000; }
        .chat-messages { height: 300px; overflow-y: auto; padding: 10px; color: white; }
        .chat-input { position: absolute; bottom: 0; left: 0; right: 0; padding: 10px; }
        .chat-input input { width: 100%; padding: 8px; border: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="video-container">
        <?php if (!empty($error_message)): ?>
        <div class="error">
            <h3>❌ Error</h3>
            <p><?php echo htmlspecialchars($error_message); ?></p>
            <button onclick="window.location.href='index.php'" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px;">Go Back</button>
        </div>
        <?php else: ?>
        <div class="status-bar">
            <i class="fas fa-circle text-green-500"></i> Meeting: <?php echo $meetingId; ?>
        </div>
        
        <div class="participant-count">
            <i class="fas fa-users"></i> <span id="participant-count">1</span>
        </div>
        
        <?php if (!empty($success_message)): ?>
        <div class="success">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="loading" id="loading">
            <i class="fas fa-spinner fa-spin"></i> Starting video conference...
        </div>
        
        <div class="meeting-container" id="meeting-container">
            <!-- VideoSDK meeting will be rendered here -->
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
            <button class="control-btn" id="chat-btn" onclick="toggleChat()" style="background: #f59e0b;">
                <i class="fas fa-comments"></i>
            </button>
            <button class="control-btn hangup" id="hangup-btn" onclick="hangup()">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
        
        <div class="chat-panel" id="chat-panel">
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
            let participantCount = 1;
            
            const meetingId = '<?php echo $meetingId; ?>';
            const userRole = '<?php echo $userRole; ?>';
            const userId = '<?php echo $user["id"]; ?>';
            const userName = '<?php echo $user["full_name"]; ?>';
            const apiKey = '<?php echo $videosdk_api_key; ?>';
            
            // Initialize VideoSDK meeting
            async function initVideoSDKMeeting() {
                try {
                    document.getElementById('loading').style.display = 'block';
                    
                    // Get authentication token
                    const tokenResponse = await fetch('videosdk-token.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `meetingId=${meetingId}`
                    });
                    
                    const tokenData = await tokenResponse.json();
                    
                    if (!tokenData.success) {
                        throw new Error('Failed to get authentication token');
                    }
                    
                    // Configure VideoSDK
                    VideoSDK.config(apiKey);
                    
                    // Create meeting with token
                    meeting = VideoSDK.initMeeting({
                        meetingId: meetingId,
                        name: userName,
                        micEnabled: true,
                        webcamEnabled: true,
                        participantId: userId,
                        token: tokenData.token
                    });
                    
                    // Set up event listeners
                    setupMeetingEvents();
                    
                    // Join meeting
                    meeting.join();
                    
                    console.log('VideoSDK meeting initialized successfully');
                    
                } catch (error) {
                    console.error('Error initializing VideoSDK meeting:', error);
                    document.getElementById('loading').innerHTML = 'Error starting meeting. Please try again.';
                }
            }
            
            // Set up meeting event listeners
            function setupMeetingEvents() {
                // Meeting joined
                meeting.on("meeting-joined", () => {
                    document.getElementById('loading').style.display = 'none';
                    console.log('Successfully joined meeting');
                });
                
                // Participant joined
                meeting.on("participant-joined", (participant) => {
                    console.log('Participant joined:', participant);
                    updateParticipantCount();
                });
                
                // Participant left
                meeting.on("participant-left", (participant) => {
                    console.log('Participant left:', participant);
                    updateParticipantCount();
                });
                
                // Meeting left
                meeting.on("meeting-left", () => {
                    console.log('Left meeting');
                    window.location.href = 'index.php';
                });
                
                // Error handling
                meeting.on("error", (error) => {
                    console.error('Meeting error:', error);
                    document.getElementById('loading').innerHTML = 'Error in meeting. Please try again.';
                });
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
                    screenBtn.style.background = '#ef4444';
                    screenBtn.innerHTML = '<i class="fas fa-stop"></i>';
                }
            }
            
            // Stop screen sharing
            function stopScreenShare() {
                if (meeting) {
                    meeting.disableScreenShare();
                    isScreenSharing = false;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.style.background = '#8b5cf6';
                    screenBtn.innerHTML = '<i class="fas fa-desktop"></i>';
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
                        addChatMessage(userName, message);
                        input.value = '';
                    }
                }
            }
            
            // Add chat message
            function addChatMessage(sender, message) {
                const chatMessages = document.getElementById('chat-messages');
                const messageDiv = document.createElement('div');
                messageDiv.innerHTML = `<strong>${sender}:</strong> ${message}`;
                messageDiv.style.marginBottom = '5px';
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Hangup
            function hangup() {
                if (meeting) {
                    meeting.leave();
                }
                window.location.href = 'index.php';
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
