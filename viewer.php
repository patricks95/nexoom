<?php
session_start();
$meetingId = isset($_GET['meeting']) ? $_GET['meeting'] : 'broadcast_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://meet.jit.si/external_api.js"></script>
    <style>
        .video-container { 
            height: 100vh; 
            position: relative;
            overflow: hidden;
        }
        .controls { 
            background: rgba(0,0,0,0.9); 
            backdrop-filter: blur(10px);
            z-index: 1000;
        }
        #jitsi-meet {
            height: 100vh;
            width: 100%;
        }
        .raise-hand { 
            animation: wave 0.5s ease-in-out; 
        }
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }
        /* Hide Jitsi's default toolbar and modals */
        .jitsi-meet {
            height: 100vh !important;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="video-container">
        <div id="jitsi-meet"></div>
        <div class="controls fixed bottom-0 left-0 right-0 p-4 flex justify-center space-x-4">
            <button id="mic-toggle" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="speaker-toggle" class="bg-green-600 hover:bg-green-700 text-white p-3 rounded-full">
                <i class="fas fa-volume-up"></i>
            </button>
            <button id="raise-hand" class="bg-yellow-600 hover:bg-yellow-700 text-white p-3 rounded-full">
                <i class="fas fa-hand-paper"></i>
            </button>
            <button id="leave-meeting" class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-full">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>

    <script>
        const options = {
            roomName: '<?php echo $meetingId; ?>',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#jitsi-meet'),
            configOverwrite: {
                startWithAudioMuted: true,
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
                toolbarButtons: ['microphone', 'camera', 'chat', 'closedcaptions'],
                filmstripOnly: false,
                disablePolls: false,
                disableReactions: false,
                disableKnocking: false
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: ['microphone', 'camera', 'chat', 'closedcaptions', 'settings', 'fullscreen', 'hangup'],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_POWERED_BY: false,
                SHOW_BRAND_WATERMARK: false,
                SHOW_POLICY_WATERMARK: false,
                SHOW_MEETING_TIMER: true,
                SHOW_ATTENDEE_COUNT: true,
                SHOW_CLOSE_BUTTON: false,
                TOOLBAR_TIMEOUT: 4000,
                DEFAULT_BACKGROUND: '#040404',
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

        const api = new JitsiMeetExternalAPI('meet.jit.si', options);
        
        // Control handlers
        let micOn = false;
        let speakerOn = true;
        let handRaised = false;

        // Mic toggle
        document.getElementById('mic-toggle').onclick = function() {
            api.executeCommand('toggleAudio');
            micOn = !micOn;
            this.innerHTML = micOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
            this.className = micOn ? 'bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full' : 'bg-red-600 hover:bg-red-700 text-white p-3 rounded-full';
        };

        // Speaker toggle
        document.getElementById('speaker-toggle').onclick = function() {
            api.executeCommand('toggleAudio');
            speakerOn = !speakerOn;
            this.innerHTML = speakerOn ? '<i class="fas fa-volume-up"></i>' : '<i class="fas fa-volume-mute"></i>';
            this.className = speakerOn ? 'bg-green-600 hover:bg-green-700 text-white p-3 rounded-full' : 'bg-red-600 hover:bg-red-700 text-white p-3 rounded-full';
        };

        // Raise hand toggle
        document.getElementById('raise-hand').onclick = function() {
            handRaised = !handRaised;
            if (handRaised) {
                this.classList.add('raise-hand');
                this.innerHTML = '<i class="fas fa-hand-paper"></i>';
                this.className = 'bg-yellow-500 hover:bg-yellow-600 text-white p-3 rounded-full raise-hand';
                // Send raise hand message to chat
                api.executeCommand('sendChatMessage', '🙋‍♂️ Student raised hand!');
            } else {
                this.classList.remove('raise-hand');
                this.innerHTML = '<i class="fas fa-hand-paper"></i>';
                this.className = 'bg-yellow-600 hover:bg-yellow-700 text-white p-3 rounded-full';
            }
        };

        // Leave meeting
        document.getElementById('leave-meeting').onclick = function() {
            if (confirm('Are you sure you want to leave the meeting?')) {
                api.dispose();
                window.location.href = 'index.php';
            }
        };

        // Event listeners
        api.addEventListeners({
            videoConferenceJoined: function () {
                console.log('Joined as viewer');
            },
            videoConferenceLeft: function () {
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>
