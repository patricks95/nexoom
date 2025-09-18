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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://meet.jit.si/external_api.js"></script>
    <style>
        body { margin: 0; padding: 0; background: #000; font-family: Arial, sans-serif; }
        .container { position: relative; width: 100vw; height: 100vh; }
        #jitsi-meet { width: 100%; height: 100%; }
        .status { position: fixed; top: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; z-index: 1000; }
        .error { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); color: white; padding: 30px; border-radius: 10px; text-align: center; z-index: 1001; }
        .success { position: fixed; top: 20px; left: 20px; background: rgba(0,255,0,0.8); color: white; padding: 10px; border-radius: 5px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error_message)): ?>
        <div class="error">
            <h3>❌ Error</h3>
            <p><?php echo htmlspecialchars($error_message); ?></p>
            <button onclick="window.location.href='index.php'" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px;">Go Back</button>
        </div>
        <?php else: ?>
        <div class="status">
            <i class="fas fa-circle text-green-500"></i> Connected to: <?php echo $meetingId; ?>
        </div>
        
        <?php if (!empty($success_message)): ?>
        <div class="success">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <div id="jitsi-meet"></div>
        
        <script>
            const options = {
                roomName: '<?php echo $meetingId; ?>',
                width: '100%',
                height: '100%',
                parentNode: document.querySelector('#jitsi-meet'),
                userInfo: {
                    displayName: '<?php echo $userRole === 'broadcaster' ? 'Broadcaster' : 'Viewer'; ?>',
                    email: 'user@nexoom.local'
                },
                configOverwrite: {
                    startWithAudioMuted: <?php echo $userRole === 'viewer' ? 'true' : 'false'; ?>,
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
                    enableClosePage: false,
                    disableThirdPartyRequests: false,
                    enableNoAudioDetection: false,
                    enableNoisyMicDetection: false,
                    enableTalkWhileMuted: false,
                    enableLayerSuspension: true,
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
            
            api.addEventListeners({
                videoConferenceJoined: function () {
                    console.log('Joined Jitsi meeting successfully');
                },
                videoConferenceLeft: function () {
                    window.location.href = 'index.php';
                },
                readyToClose: function () {
                    window.location.href = 'index.php';
                }
            });
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
