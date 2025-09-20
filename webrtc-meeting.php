<?php
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$meetingId = isset($_GET['room']) ? $_GET['room'] : 'meeting_' . uniqid();
$userRole = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Meeting - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
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
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            border-radius: 20px;
            overflow: hidden;
            min-height: 200px;
            border: 3px solid #4a7c59;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .video-item:hover {
            border-color: #d4af37;
            transform: scale(1.05) translateY(-5px);
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.3);
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
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            z-index: 10;
            backdrop-filter: blur(15px);
            border: 2px solid #4a7c59;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            padding: 25px 50px;
            border-radius: 60px;
            display: flex;
            gap: 25px;
            z-index: 1000;
            backdrop-filter: blur(25px);
            border: 3px solid #4a7c59;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        
        .control-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid #4a7c59;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
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
            background: linear-gradient(45deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        
        .control-btn:hover::before {
            transform: translateX(100%);
        }
        
        .control-btn:hover {
            transform: scale(1.15) translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        .control-btn.mic {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            border-color: #4a7c59;
        }
        
        .control-btn.mic.muted {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-color: #ef4444;
        }
        
        .control-btn.video {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            border-color: #4a7c59;
        }
        
        .control-btn.video.muted {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-color: #ef4444;
        }
        
        .control-btn.screen {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            border-color: #d4af37;
        }
        
        .control-btn.screen.active {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-color: #ef4444;
        }
        
        .control-btn.chat {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            border-color: #d4af37;
        }
        
        .control-btn.hangup {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-color: #ef4444;
        }
        
        .status-bar {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            padding: 18px 30px;
            border-radius: 30px;
            z-index: 1000;
            backdrop-filter: blur(25px);
            border: 3px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .participant-count {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            padding: 18px 30px;
            border-radius: 30px;
            z-index: 1000;
            backdrop-filter: blur(25px);
            border: 3px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #d4af37;
            font-size: 22px;
            z-index: 999;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(212, 175, 55, 0.3);
            border-top: 5px solid #d4af37;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #dc2626;
            padding: 50px;
            border-radius: 25px;
            text-align: center;
            z-index: 1001;
            backdrop-filter: blur(25px);
            border: 3px solid #dc2626;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        
        .success {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            padding: 18px 35px;
            border-radius: 30px;
            z-index: 1000;
            backdrop-filter: blur(25px);
            border: 3px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .meeting-info {
            position: fixed;
            top: 80px;
            left: 20px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            padding: 20px;
            border-radius: 20px;
            z-index: 1000;
            backdrop-filter: blur(25px);
            border: 3px solid #4a7c59;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            font-weight: 600;
            max-width: 300px;
        }
        
        .meeting-info h4 {
            margin-bottom: 10px;
            color: #d4af37;
            font-size: 16px;
        }
        
        .meeting-info p {
            margin-bottom: 5px;
            font-size: 14px;
            color: #a0a0a0;
        }
    </style>
</head>
<body>
    <div class="meeting-container">
        <div class="status-bar">
            <i class="fas fa-circle text-green-500"></i> 
            <span style="margin-left: 10px;">Meeting: <?php echo htmlspecialchars($meetingId); ?></span>
        </div>
        
        <div class="participant-count">
            <i class="fas fa-users"></i> 
            <span style="margin-left: 10px;" id="participant-count">1</span>
        </div>
        
        <div class="meeting-info">
            <h4>Meeting Details</h4>
            <p><strong>Room ID:</strong> <?php echo htmlspecialchars($meetingId); ?></p>
            <p><strong>Participant:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Status:</strong> <span id="connection-status">Connecting...</span></p>
        </div>
        
        <div class="success" id="success-message" style="display: none;">
            ✅ Meeting Started Successfully!
        </div>
        
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
            <button class="control-btn hangup" id="hangup-btn" onclick="hangup()">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
    </div>
    
    <script>
        let localStream = null;
        let isMicOn = true;
        let isVideoOn = true;
        let isScreenSharing = false;
        let participantCount = 1;
        
        // Initialize meeting
        async function initMeeting() {
            try {
                console.log('Initializing WebRTC meeting...');
                
                // Request camera and microphone access
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                
                // Add local video
                addLocalVideo();
                
                // Update status
                updateConnectionStatus('Connected');
                hideLoading();
                showSuccess('Meeting Started Successfully!');
                
                console.log('Meeting initialized successfully');
                
            } catch (error) {
                console.error('Error initializing meeting:', error);
                showError('Failed to access camera/microphone: ' + error.message);
            }
        }
        
        function addLocalVideo() {
            const videoGrid = document.getElementById('video-grid');
            const videoItem = document.createElement('div');
            videoItem.className = 'video-item';
            videoItem.id = 'local-video';
            
            const video = document.createElement('video');
            video.autoplay = true;
            video.playsInline = true;
            video.muted = true;
            video.srcObject = localStream;
            
            const label = document.createElement('div');
            label.className = 'video-label';
            label.textContent = 'You (<?php echo $user['full_name']; ?>)';
            
            videoItem.appendChild(video);
            videoItem.appendChild(label);
            videoGrid.appendChild(videoItem);
        }
        
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
        
        async function toggleScreenShare() {
            try {
                if (!isScreenSharing) {
                    // Start screen sharing
                    const screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: true,
                        audio: true
                    });
                    
                    // Replace video track
                    const videoTracks = localStream.getVideoTracks();
                    const screenTracks = screenStream.getVideoTracks();
                    
                    if (videoTracks.length > 0 && screenTracks.length > 0) {
                        localStream.removeTrack(videoTracks[0]);
                        localStream.addTrack(screenTracks[0]);
                        
                        // Update video element
                        const video = document.querySelector('#local-video video');
                        if (video) {
                            video.srcObject = localStream;
                        }
                    }
                    
                    isScreenSharing = true;
                    const screenBtn = document.getElementById('screen-btn');
                    screenBtn.classList.add('active');
                    screenBtn.innerHTML = '<i class="fas fa-stop"></i>';
                    
                    // Handle screen share end
                    screenTracks[0].onended = () => {
                        stopScreenShare();
                    };
                    
                } else {
                    stopScreenShare();
                }
            } catch (error) {
                console.error('Error toggling screen share:', error);
                showError('Failed to start screen sharing: ' + error.message);
            }
        }
        
        function stopScreenShare() {
            // This is a simplified version - in a real implementation,
            // you'd need to properly handle track replacement
            isScreenSharing = false;
            const screenBtn = document.getElementById('screen-btn');
            screenBtn.classList.remove('active');
            screenBtn.innerHTML = '<i class="fas fa-desktop"></i>';
        }
        
        function toggleChat() {
            // Chat functionality can be added here
            alert('Chat feature coming soon!');
        }
        
        function hangup() {
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    track.stop();
                });
            }
            window.location.href = 'home.php';
        }
        
        function updateConnectionStatus(status) {
            document.getElementById('connection-status').textContent = status;
        }
        
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        function showSuccess(message) {
            const successEl = document.getElementById('success-message');
            successEl.textContent = '✅ ' + message;
            successEl.style.display = 'block';
            setTimeout(() => {
                successEl.style.display = 'none';
            }, 3000);
        }
        
        function showError(message) {
            const errorEl = document.createElement('div');
            errorEl.className = 'error';
            errorEl.innerHTML = `
                <h3 style="font-size: 24px; margin-bottom: 20px;">❌ Error</h3>
                <p style="font-size: 16px; margin-bottom: 30px;">${message}</p>
                <button onclick="window.location.href='home.php'" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; padding: 15px 30px; border-radius: 25px; cursor: pointer; font-size: 16px; font-weight: 600;">
                    Go Back
                </button>
            `;
            document.body.appendChild(errorEl);
        }
        
        // Initialize when page loads
        window.addEventListener('load', initMeeting);
        
        // Handle page unload
        window.addEventListener('beforeunload', function() {
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    track.stop();
                });
            }
        });
    </script>
</body>
</html>
