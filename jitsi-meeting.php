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
$error_message = '';

// Validate meeting ID
if (empty($meetingId)) {
    header('Location: index.php');
    exit();
}

// Get meeting details from database
$meetingData = $meeting->getMeeting($meetingId);
$isValidMeeting = $meetingData !== false;

// If meeting doesn't exist, create it (for broadcasters)
if (!$isValidMeeting && $userRole === 'broadcaster') {
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
        $isValidMeeting = true;
    } else {
        $error_message = $result['message'];
    }
}

// Join meeting if valid
if ($isValidMeeting && empty($error_message)) {
    $joinResult = $meeting->joinMeeting($meetingId, $user['id'], $userRole === 'broadcaster' ? 'host' : 'participant');
    if (!$joinResult['success']) {
        $error_message = $joinResult['message'];
    }
} elseif (!$isValidMeeting) {
    $error_message = 'Meeting not found or you do not have permission to join';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($userRole); ?> - Nexoom Meeting</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://meet.jit.si/external_api.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .video-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #000;
        }
        
        #jitsi-meet {
            width: 100%;
            height: 100%;
        }
        
        .meeting-info {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 1000;
        }
        
        .status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 1000;
        }
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 18px;
            z-index: 999;
        }
        
        .error-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            z-index: 1001;
            max-width: 500px;
        }
        
        .error-message h3 {
            color: #f59e0b;
            margin-bottom: 15px;
        }
        
        .error-message p {
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .error-message button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        
        .error-message button:hover {
            background: #2563eb;
        }
        
        .meeting-validation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            z-index: 1001;
            max-width: 500px;
        }
        
        .meeting-validation h3 {
            color: #10b981;
            margin-bottom: 15px;
        }
        
        .meeting-validation p {
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .meeting-validation button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        
        .meeting-validation button:hover {
            background: #2563eb;
        }
        
        .custom-controls {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 10px 20px;
            display: flex;
            gap: 15px;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .control-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .control-btn:hover {
            transform: scale(1.1);
        }
        
        .control-btn:active {
            transform: scale(0.95);
        }
        
        .mic-btn { background: #3b82f6; }
        .mic-btn:hover { background: #2563eb; }
        .mic-btn.muted { background: #ef4444; }
        
        .video-btn { background: #10b981; }
        .video-btn:hover { background: #059669; }
        .video-btn.muted { background: #ef4444; }
        
        .screen-btn { background: #8b5cf6; }
        .screen-btn:hover { background: #7c3aed; }
        .screen-btn.active { background: #ef4444; }
        
        .hand-btn { background: #f59e0b; }
        .hand-btn:hover { background: #d97706; }
        .hand-btn.raised { background: #fbbf24; animation: wave 0.5s ease-in-out; }
        
        .leave-btn { background: #ef4444; }
        .leave-btn:hover { background: #dc2626; }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }
    </style>
</head>
<body>
    <div class="video-container">
        <div class="meeting-info">
            <strong>Meeting ID:</strong> <?php echo $meetingId; ?><br>
            <strong>Role:</strong> <?php echo ucfirst($userRole); ?>
        </div>
        
        <div class="status" id="status">
            <i class="fas fa-circle text-red-500"></i> Validating meeting...
        </div>
        
        <div class="loading" id="loading">
            <i class="fas fa-spinner fa-spin"></i> Connecting to Jitsi Meet...
        </div>
        
        <div class="meeting-validation" id="meetingValidation" style="display: none;">
            <h3><i class="fas fa-check-circle"></i> Meeting Validated</h3>
            <p>Meeting ID: <strong><?php echo $meetingId; ?></strong></p>
            <p>Status: <strong>Active</strong></p>
            <p>Platform: <strong>Jitsi Meet</strong></p>
            <button onclick="startJitsiMeeting()">Join Meeting</button>
            <button onclick="window.location.href='index.php'">Go Back</button>
        </div>
        
        <div class="error-message" id="errorMessage" style="display: <?php echo !empty($error_message) ? 'block' : 'none'; ?>;">
            <h3><i class="fas fa-exclamation-triangle"></i> Meeting Error</h3>
            <p id="errorText"><?php echo !empty($error_message) ? htmlspecialchars($error_message) : 'The meeting you\'re trying to join is not available.'; ?></p>
            <button onclick="retryConnection()">Try Again</button>
            <button onclick="window.location.href='index.php'">Go Back</button>
        </div>
        
        <div id="jitsi-meet"></div>
        
        <div class="custom-controls" id="customControls" style="display: none;">
            <button id="mic-toggle" class="control-btn mic-btn" title="Toggle Microphone">
                <i class="fas fa-microphone"></i>
            </button>
            
            <button id="video-toggle" class="control-btn video-btn" title="Toggle Video">
                <i class="fas fa-video"></i>
            </button>
            
            <?php if ($userRole === 'broadcaster'): ?>
            <button id="screen-share" class="control-btn screen-btn" title="Share Screen">
                <i class="fas fa-desktop"></i>
            </button>
            <?php else: ?>
            <button id="raise-hand" class="control-btn hand-btn" title="Raise Hand">
                <i class="fas fa-hand-paper"></i>
            </button>
            <?php endif; ?>
            
            <button id="leave-meeting" class="control-btn leave-btn" title="Leave Meeting">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
    </div>

    <script>
        // Jitsi Meet integration with meeting validation
        class JitsiMeeting {
            constructor() {
                this.api = null;
                this.isMuted = false;
                this.isVideoOn = true;
                this.isScreenSharing = false;
                this.handRaised = false;
                this.meetingId = '<?php echo $meetingId; ?>';
                this.userRole = '<?php echo $userRole; ?>';
                this.isValidMeeting = <?php echo $isValidMeeting ? 'true' : 'false'; ?>;
                
                this.init();
            }
            
            init() {
                // Check if there's an error message
                const errorElement = document.getElementById('errorMessage');
                if (errorElement && errorElement.style.display !== 'none') {
                    this.showMeetingError();
                    return;
                }
                
                // First validate the meeting
                if (!this.isValidMeeting) {
                    this.showMeetingError();
                    return;
                }
                
                // Show meeting validation
                this.showMeetingValidation();
            }
            
            showMeetingValidation() {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('meetingValidation').style.display = 'block';
                this.updateStatus('Meeting validated', 'yellow');
            }
            
            showMeetingError() {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('errorText').textContent = 'Meeting ID "' + this.meetingId + '" is not valid or has expired.';
                this.updateStatus('Invalid meeting', 'red');
            }
            
            startJitsiMeeting() {
                try {
                    document.getElementById('meetingValidation').style.display = 'none';
                    document.getElementById('loading').style.display = 'block';
                    
                    const options = {
                        roomName: this.meetingId,
                        width: '100%',
                        height: '100%',
                        parentNode: document.querySelector('#jitsi-meet'),
                        userInfo: {
                            displayName: this.userRole === 'broadcaster' ? 'Broadcaster' : 'Viewer',
                            email: 'user@nexoom.local'
                        },
                        configOverwrite: {
                            startWithAudioMuted: this.userRole === 'viewer',
                            startWithVideoMuted: false,
                            enableWelcomePage: false,
                            prejoinPageEnabled: false,
                            requireDisplayName: false,
                            enableUserRolesBasedOnToken: false,
                            enableInsecureRoomNameWarning: false,
                            enableNoisyMicDetection: false,
                            enableTalkWhileMuted: false,
                            disableModeratorIndicator: false,
                            startScreenSharing: false,
                            enableEmailInStats: false,
                            toolbarButtons: ['microphone', 'camera', 'closedcaptions', 'desktop', 'recording', 'livestreaming', 'chat'],
                            filmstripOnly: false,
                            disablePolls: false,
                            disableReactions: false,
                            disableKnocking: false
                        },
                        interfaceConfigOverwrite: {
                            TOOLBAR_BUTTONS: ['microphone', 'camera', 'desktop', 'recording', 'livestreaming', 'chat', 'invite', 'closedcaptions', 'settings', 'fullscreen', 'hangup'],
                            SHOW_JITSI_WATERMARK: false,
                            SHOW_WATERMARK_FOR_GUESTS: false,
                            SHOW_POWERED_BY: false,
                            SHOW_BRAND_WATERMARK: false,
                            SHOW_POLICY_WATERMARK: false,
                            SHOW_MEETING_TIMER: true,
                            SHOW_ATTENDEE_COUNT: true,
                            SHOW_CLOSE_BUTTON: false,
                            TOOLBAR_TIMEOUT: 4000,
                            DEFAULT_BACKGROUND: '#000000',
                            INITIAL_TOOLBAR_TIMEOUT: 20000,
                            TOOLBAR_ALWAYS_VISIBLE: false,
                            SETTINGS_SECTIONS: ['devices', 'language'],
                            SHOW_DEEP_LINKING_PAGE: false,
                            DISABLE_DOMINANT_SPEAKER_INDICATOR: false,
                            DISABLE_FOCUS_INDICATOR: false,
                            DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
                            DISABLE_PRESENCE_STATUS: false,
                            DISABLE_RINGING: false,
                            AUDIO_LEVEL_PRIMARY_COLOR: 'rgba(255,255,255,0.4)',
                            AUDIO_LEVEL_SECONDARY_COLOR: 'rgba(255,255,255,0.2)'
                        }
                    };
                    
                    this.api = new JitsiMeetExternalAPI('meet.jit.si', options);
                    
                    // Event listeners
                    this.api.addEventListeners({
                        videoConferenceJoined: () => {
                            document.getElementById('loading').style.display = 'none';
                            document.getElementById('customControls').style.display = 'flex';
                            this.updateStatus('Connected to Jitsi Meet', 'green');
                            this.setupCustomControls();
                        },
                        videoConferenceLeft: () => {
                            window.location.href = 'index.php';
                        },
                        readyToClose: () => {
                            window.location.href = 'index.php';
                        }
                    });
                    
                } catch (error) {
                    console.error('Error starting Jitsi meeting:', error);
                    this.handleConnectionError(error);
                }
            }
            
            setupCustomControls() {
                // Microphone toggle
                document.getElementById('mic-toggle').onclick = () => {
                    this.api.executeCommand('toggleAudio');
                    this.isMuted = !this.isMuted;
                    const btn = document.getElementById('mic-toggle');
                    btn.classList.toggle('muted', this.isMuted);
                    btn.innerHTML = this.isMuted ? '<i class="fas fa-microphone-slash"></i>' : '<i class="fas fa-microphone"></i>';
                };
                
                // Video toggle
                document.getElementById('video-toggle').onclick = () => {
                    this.api.executeCommand('toggleVideo');
                    this.isVideoOn = !this.isVideoOn;
                    const btn = document.getElementById('video-toggle');
                    btn.classList.toggle('muted', !this.isVideoOn);
                    btn.innerHTML = this.isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
                };
                
                // Screen share (broadcaster only)
                <?php if ($userRole === 'broadcaster'): ?>
                document.getElementById('screen-share').onclick = () => {
                    this.api.executeCommand('toggleShareScreen');
                    this.isScreenSharing = !this.isScreenSharing;
                    const btn = document.getElementById('screen-share');
                    btn.classList.toggle('active', this.isScreenSharing);
                    btn.innerHTML = this.isScreenSharing ? '<i class="fas fa-stop"></i>' : '<i class="fas fa-desktop"></i>';
                };
                <?php else: ?>
                // Raise hand (viewer only)
                document.getElementById('raise-hand').onclick = () => {
                    this.handRaised = !this.handRaised;
                    const btn = document.getElementById('raise-hand');
                    btn.classList.toggle('raised', this.handRaised);
                    
                    if (this.handRaised) {
                        this.api.executeCommand('sendChatMessage', '🙋‍♂️ Student raised hand!');
                    }
                };
                <?php endif; ?>
                
                // Leave meeting
                document.getElementById('leave-meeting').onclick = () => {
                    if (confirm('Are you sure you want to leave the meeting?')) {
                        this.api.dispose();
                        window.location.href = 'index.php';
                    }
                };
            }
            
            handleConnectionError(error) {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('errorText').textContent = 'Failed to connect to Jitsi Meet: ' + error.message;
                this.updateStatus('Connection Error', 'red');
            }
            
            updateStatus(text, color) {
                const status = document.getElementById('status');
                status.innerHTML = `<i class="fas fa-circle text-${color}-500"></i> ${text}`;
            }
        }
        
        // Global functions
        function startJitsiMeeting() {
            if (window.jitsiMeeting) {
                window.jitsiMeeting.startJitsiMeeting();
            }
        }
        
        function retryConnection() {
            if (window.jitsiMeeting) {
                window.jitsiMeeting.startJitsiMeeting();
            }
        }
        
        // Initialize Jitsi meeting when page loads
        document.addEventListener('DOMContentLoaded', () => {
            window.jitsiMeeting = new JitsiMeeting();
        });
    </script>
</body>
</html>
