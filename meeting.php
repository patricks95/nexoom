<?php
session_start();

// Generate a unique meeting ID
$meetingId = isset($_GET['room']) ? $_GET['room'] : 'nexoom_' . substr(md5(uniqid()), 0, 8);
$userRole = isset($_GET['role']) ? $_GET['role'] : 'viewer';

// Store meeting info in session
$_SESSION['meeting_id'] = $meetingId;
$_SESSION['user_role'] = $userRole;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($userRole); ?> - Nexoom</title>
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
        }
        
        #jitsi-meet {
            width: 100%;
            height: 100%;
        }
        
        .controls {
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
        
        .speaker-btn { background: #10b981; }
        .speaker-btn:hover { background: #059669; }
        .speaker-btn.muted { background: #ef4444; }
        
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
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 18px;
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <div class="meeting-info">
            <strong>Meeting ID:</strong> <?php echo $meetingId; ?><br>
            <strong>Role:</strong> <?php echo ucfirst($userRole); ?>
        </div>
        
        <div class="loading" id="loading">
            <i class="fas fa-spinner fa-spin"></i> Connecting to meeting...
        </div>
        
        <div id="jitsi-meet"></div>
        
        <div class="controls">
            <button id="mic-toggle" class="control-btn mic-btn" title="Toggle Microphone">
                <i class="fas fa-microphone"></i>
            </button>
            
            <button id="speaker-toggle" class="control-btn speaker-btn" title="Toggle Speaker">
                <i class="fas fa-volume-up"></i>
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
        const options = {
            roomName: '<?php echo $meetingId; ?>',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#jitsi-meet'),
            userInfo: {
                displayName: '<?php echo ucfirst($userRole); ?> User',
                email: 'user@nexoom.local'
            },
            configOverwrite: {
                startWithAudioMuted: <?php echo $userRole === 'broadcaster' ? 'false' : 'true'; ?>,
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
                enableLayerSuspension: true,
                channelLastN: -1,
                startBitrate: '800',
                resolution: 720,
                maxReceiveBitrate: 1000000,
                enableRemb: true,
                enableTcc: true,
                useStunTurn: true,
                enableIceRestart: true,
                useRoomAsSharedDocumentName: true,
                enableClosePage: false,
                disableThirdPartyRequests: false,
                enableNoAudioDetection: false,
                enableNoisyMicDetection: false,
                enableTalkWhileMuted: false,
                enableLayerSuspension: true,
                enableLipSync: false,
                enableRemb: true,
                enableTcc: true,
                useStunTurn: true,
                enableIceRestart: true,
                useRoomAsSharedDocumentName: true,
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
                AUDIO_LEVEL_SECONDARY_COLOR: 'rgba(255,255,255,0.2)',
                VIDEO_QUALITY_LABEL_DISABLED: false,
                CONNECTION_INDICATOR_HIDE_AFTER: 5,
                CONNECTION_INDICATOR_AUTO_HIDE_ENABLED: true,
                CONNECTION_INDICATOR_AUTO_HIDE_TIMEOUT: 5000,
                CONNECTION_INDICATOR_DISABLED: false
            }
        };

        const api = new JitsiMeetExternalAPI('meet.jit.si', options);
        
        // State variables
        let micOn = <?php echo $userRole === 'broadcaster' ? 'true' : 'false'; ?>;
        let speakerOn = true;
        let screenShareOn = false;
        let handRaised = false;

        // Hide loading when ready
        api.addEventListeners({
            videoConferenceJoined: function () {
                document.getElementById('loading').style.display = 'none';
                console.log('Joined meeting as <?php echo $userRole; ?>');
            },
            videoConferenceLeft: function () {
                window.location.href = 'index.php';
            },
            readyToClose: function () {
                window.location.href = 'index.php';
            }
        });

        // Microphone toggle
        document.getElementById('mic-toggle').onclick = function() {
            api.executeCommand('toggleAudio');
            micOn = !micOn;
            this.classList.toggle('muted', !micOn);
            this.innerHTML = micOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
        };

        // Speaker toggle
        document.getElementById('speaker-toggle').onclick = function() {
            api.executeCommand('toggleAudio');
            speakerOn = !speakerOn;
            this.classList.toggle('muted', !speakerOn);
            this.innerHTML = speakerOn ? '<i class="fas fa-volume-up"></i>' : '<i class="fas fa-volume-mute"></i>';
        };

        <?php if ($userRole === 'broadcaster'): ?>
        // Screen share toggle
        document.getElementById('screen-share').onclick = function() {
            api.executeCommand('toggleShareScreen');
            screenShareOn = !screenShareOn;
            this.classList.toggle('active', screenShareOn);
            this.innerHTML = screenShareOn ? '<i class="fas fa-stop"></i>' : '<i class="fas fa-desktop"></i>';
        };
        <?php else: ?>
        // Raise hand toggle
        document.getElementById('raise-hand').onclick = function() {
            handRaised = !handRaised;
            this.classList.toggle('raised', handRaised);
            if (handRaised) {
                api.executeCommand('sendChatMessage', '🙋‍♂️ Student raised hand!');
            }
        };
        <?php endif; ?>

        // Leave meeting
        document.getElementById('leave-meeting').onclick = function() {
            if (confirm('Are you sure you want to leave the meeting?')) {
                api.dispose();
                window.location.href = 'index.php';
            }
        };
    </script>
</body>
</html>
