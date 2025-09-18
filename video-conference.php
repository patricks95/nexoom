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
    <title>Video Conference - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #1a1a1a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .video-container { position: relative; width: 100vw; height: 100vh; background: #000; }
        .video-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; padding: 10px; height: calc(100vh - 80px); }
        .video-item { position: relative; background: #2a2a2a; border-radius: 10px; overflow: hidden; min-height: 200px; }
        .video-item video { width: 100%; height: 100%; object-fit: cover; }
        .video-item.local { border: 3px solid #3b82f6; }
        .video-item.remote { border: 3px solid #10b981; }
        .video-label { position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; z-index: 10; }
        .controls { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.8); padding: 15px 30px; border-radius: 50px; display: flex; gap: 15px; z-index: 1000; }
        .control-btn { width: 50px; height: 50px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.3s; }
        .control-btn.mic { background: #10b981; color: white; }
        .control-btn.mic.muted { background: #ef4444; }
        .control-btn.video { background: #3b82f6; color: white; }
        .control-btn.video.muted { background: #ef4444; }
        .control-btn.screen { background: #8b5cf6; color: white; }
        .control-btn.hangup { background: #ef4444; color: white; }
        .control-btn:hover { transform: scale(1.1); }
        .status-bar { position: fixed; top: 20px; left: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .participant-count { position: fixed; top: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .error { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.9); color: white; padding: 30px; border-radius: 10px; text-align: center; z-index: 1001; }
        .success { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,255,0,0.8); color: white; padding: 10px 20px; border-radius: 25px; z-index: 1000; }
        .loading { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 18px; z-index: 999; }
        .hand-raised { position: absolute; top: 10px; right: 10px; background: #f59e0b; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; z-index: 10; }
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
            <button class="control-btn hangup" id="hangup-btn" onclick="hangup()">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
        
        <script>
            let localStream = null;
            let remoteStreams = new Map();
            let peerConnections = new Map();
            let isMicOn = true;
            let isVideoOn = true;
            let isScreenSharing = false;
            let localVideo = null;
            let participantCount = 1;
            
            const meetingId = '<?php echo $meetingId; ?>';
            const userRole = '<?php echo $userRole; ?>';
            const userId = '<?php echo $user["id"]; ?>';
            const userName = '<?php echo $user["full_name"]; ?>';
            
            // STUN servers for WebRTC
            const iceServers = {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                    { urls: 'stun:stun2.l.google.com:19302' }
                ]
            };
            
            // Initialize the video conference
            async function initVideoConference() {
                try {
                    document.getElementById('loading').style.display = 'block';
                    
                    // Get user media
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });
                    
                    // Create local video element
                    localVideo = createVideoElement(localStream, 'You', 'local');
                    document.getElementById('video-grid').appendChild(localVideo);
                    
                    // Hide loading
                    document.getElementById('loading').style.display = 'none';
                    
                    // Start signaling (simplified for demo)
                    startSignaling();
                    
                    console.log('Video conference initialized successfully');
                    
                } catch (error) {
                    console.error('Error initializing video conference:', error);
                    document.getElementById('loading').innerHTML = 'Error accessing camera/microphone. Please check permissions.';
                }
            }
            
            // Create video element
            function createVideoElement(stream, name, type) {
                const videoItem = document.createElement('div');
                videoItem.className = `video-item ${type}`;
                
                const video = document.createElement('video');
                video.srcObject = stream;
                video.autoplay = true;
                video.muted = type === 'local';
                video.playsInline = true;
                
                const label = document.createElement('div');
                label.className = 'video-label';
                label.textContent = name;
                
                videoItem.appendChild(video);
                videoItem.appendChild(label);
                
                return videoItem;
            }
            
            // Start signaling (simplified WebRTC implementation)
            function startSignaling() {
                // In a real implementation, you would use WebSocket or Socket.IO
                // For this demo, we'll simulate a working video conference
                console.log('Signaling started for meeting:', meetingId);
                
                // Simulate adding remote participants after a delay
                setTimeout(() => {
                    if (userRole === 'broadcaster') {
                        // Simulate viewer joining
                        addSimulatedParticipant('Viewer 1');
                    } else {
                        // Simulate broadcaster joining
                        addSimulatedParticipant('Broadcaster');
                    }
                }, 2000);
            }
            
            // Add simulated participant (for demo purposes)
            function addSimulatedParticipant(name) {
                // Create a simulated video stream
                const canvas = document.createElement('canvas');
                canvas.width = 640;
                canvas.height = 480;
                const ctx = canvas.getContext('2d');
                
                // Draw a simple pattern
                const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                gradient.addColorStop(0, '#3b82f6');
                gradient.addColorStop(1, '#8b5cf6');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // Add text
                ctx.fillStyle = 'white';
                ctx.font = '24px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(name, canvas.width/2, canvas.height/2);
                
                // Convert canvas to stream
                const stream = canvas.captureStream(30);
                
                // Create video element
                const videoElement = createVideoElement(stream, name, 'remote');
                document.getElementById('video-grid').appendChild(videoElement);
                
                // Update participant count
                participantCount++;
                document.getElementById('participant-count').textContent = participantCount;
            }
            
            // Toggle microphone
            function toggleMic() {
                if (localStream) {
                    const audioTracks = localStream.getAudioTracks();
                    audioTracks.forEach(track => {
                        track.enabled = !track.enabled;
                    });
                    
                    isMicOn = !isMicOn;
                    const micBtn = document.getElementById('mic-btn');
                    micBtn.classList.toggle('muted', !isMicOn);
                    micBtn.innerHTML = isMicOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
                }
            }
            
            // Toggle video
            function toggleVideo() {
                if (localStream) {
                    const videoTracks = localStream.getVideoTracks();
                    videoTracks.forEach(track => {
                        track.enabled = !track.enabled;
                    });
                    
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
            async function startScreenShare() {
                try {
                    const screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: true,
                        audio: true
                    });
                    
                    // Replace local video with screen share
                    if (localVideo) {
                        localVideo.remove();
                    }
                    
                    localVideo = createVideoElement(screenStream, 'Screen Share', 'local');
                    document.getElementById('video-grid').appendChild(localVideo);
                    
                    isScreenSharing = true;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.style.background = '#ef4444';
                    screenBtn.innerHTML = '<i class="fas fa-stop"></i>';
                    
                    // Handle screen share end
                    screenStream.getVideoTracks()[0].onended = () => {
                        stopScreenShare();
                    };
                    
                } catch (error) {
                    console.error('Error starting screen share:', error);
                }
            }
            
            // Stop screen sharing
            async function stopScreenShare() {
                try {
                    // Get back to camera
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });
                    
                    if (localVideo) {
                        localVideo.remove();
                    }
                    
                    localVideo = createVideoElement(localStream, 'You', 'local');
                    document.getElementById('video-grid').appendChild(localVideo);
                    
                    isScreenSharing = false;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.style.background = '#8b5cf6';
                    screenBtn.innerHTML = '<i class="fas fa-desktop"></i>';
                    
                } catch (error) {
                    console.error('Error stopping screen share:', error);
                }
            }
            
            // Hangup
            function hangup() {
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                }
                
                // Close all peer connections
                peerConnections.forEach(pc => pc.close());
                peerConnections.clear();
                
                // Redirect to index
                window.location.href = 'index.php';
            }
            
            // Initialize when page loads
            window.addEventListener('load', initVideoConference);
            
            // Handle page unload
            window.addEventListener('beforeunload', hangup);
            
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
