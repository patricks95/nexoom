<?php
// Simple WebRTC implementation without VideoSDK library issues
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'meeting_' . uniqid();
$participantName = isset($_GET['name']) && !empty(trim($_GET['name'])) ? trim($_GET['name']) : 'User_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Meeting - Nexoom (Simple WebRTC)</title>
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
            margin: 0;
            padding: 0;
        }
        
        .meeting-container {
            width: 100vw;
            height: 100vh;
            position: relative;
            background: #000;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            padding: 15px 20px;
            color: #d4af37;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #4a7c59;
        }
        
        .meeting-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .meeting-info {
            font-size: 14px;
            color: #a0a0a0;
        }
        
        .video-container {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px;
            overflow-y: auto;
        }
        
        .participant-video {
            background: #2d3748;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            min-width: 300px;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            border: 2px solid #4a7c59;
        }
        
        .participant-video video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .participant-name {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .controls {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            border-top: 2px solid #4a7c59;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-mic {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }
        
        .btn-mic:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(72, 187, 120, 0.3);
        }
        
        .btn-mic.muted {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .btn-webcam {
            background: linear-gradient(135deg, #63b3ed, #4299e1);
            color: white;
        }
        
        .btn-webcam:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 179, 237, 0.3);
        }
        
        .btn-webcam.off {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .btn-leave {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-leave:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
        
        .status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: #00ff00;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            z-index: 1000;
        }
        
        .error-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <div class="meeting-container">
        <div class="header">
            <div class="meeting-title">Nexoom Video Meeting</div>
            <div class="meeting-info">
                Meeting ID: <?php echo htmlspecialchars($meetingId); ?> | 
                User: <?php echo htmlspecialchars($participantName); ?>
            </div>
        </div>
        
        <div class="video-container" id="videoContainer">
            <div class="participant-video" id="localVideo">
                <div class="participant-name"><?php echo htmlspecialchars($participantName); ?> (You)</div>
                <video id="localVideoElement" autoplay muted playsinline></video>
            </div>
        </div>
        
        <div class="controls">
            <button class="btn btn-mic" id="micBtn">
                <i class="fas fa-microphone"></i> Mic On
            </button>
            <button class="btn btn-webcam" id="webcamBtn">
                <i class="fas fa-video"></i> Camera On
            </button>
            <button class="btn btn-leave" id="leaveBtn">
                <i class="fas fa-phone-slash"></i> Leave Meeting
            </button>
        </div>
        
        <div class="status" id="status">
            Connecting...
        </div>
        
        <div class="error-message" id="errorMessage">
            <h3>Connection Error</h3>
            <p>Unable to access camera and microphone. Please check your permissions and try again.</p>
            <button class="btn btn-mic" onclick="retryConnection()">Retry</button>
        </div>
    </div>
    
    <script>
        let localStream = null;
        let localVideo = null;
        let micEnabled = true;
        let webcamEnabled = true;
        
        // Initialize when page loads
        window.addEventListener('load', function() {
            localVideo = document.getElementById('localVideoElement');
            updateStatus('Initializing...');
            initializeMeeting();
        });
        
        async function initializeMeeting() {
            try {
                updateStatus('Requesting camera and microphone access...');
                
                // Request camera and microphone access
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                
                // Set up local video
                localVideo.srcObject = localStream;
                
                updateStatus('Connected successfully!');
                
                // Set up control buttons
                setupControls();
                
            } catch (error) {
                console.error('Error accessing media devices:', error);
                showError('Unable to access camera and microphone. Please check your permissions and try again.');
            }
        }
        
        function setupControls() {
            const micBtn = document.getElementById('micBtn');
            const webcamBtn = document.getElementById('webcamBtn');
            const leaveBtn = document.getElementById('leaveBtn');
            
            micBtn.addEventListener('click', toggleMic);
            webcamBtn.addEventListener('click', toggleWebcam);
            leaveBtn.addEventListener('click', leaveMeeting);
        }
        
        function toggleMic() {
            if (localStream) {
                const audioTracks = localStream.getAudioTracks();
                audioTracks.forEach(track => {
                    track.enabled = !track.enabled;
                });
                
                micEnabled = !micEnabled;
                const micBtn = document.getElementById('micBtn');
                
                if (micEnabled) {
                    micBtn.innerHTML = '<i class="fas fa-microphone"></i> Mic On';
                    micBtn.classList.remove('muted');
                } else {
                    micBtn.innerHTML = '<i class="fas fa-microphone-slash"></i> Mic Off';
                    micBtn.classList.add('muted');
                }
            }
        }
        
        function toggleWebcam() {
            if (localStream) {
                const videoTracks = localStream.getVideoTracks();
                videoTracks.forEach(track => {
                    track.enabled = !track.enabled;
                });
                
                webcamEnabled = !webcamEnabled;
                const webcamBtn = document.getElementById('webcamBtn');
                
                if (webcamEnabled) {
                    webcamBtn.innerHTML = '<i class="fas fa-video"></i> Camera On';
                    webcamBtn.classList.remove('off');
                } else {
                    webcamBtn.innerHTML = '<i class="fas fa-video-slash"></i> Camera Off';
                    webcamBtn.classList.add('off');
                }
            }
        }
        
        function leaveMeeting() {
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    track.stop();
                });
            }
            
            updateStatus('Leaving meeting...');
            
            // Redirect to home page
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        }
        
        function updateStatus(message) {
            document.getElementById('status').textContent = message;
        }
        
        function showError(message) {
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('errorMessage').querySelector('p').textContent = message;
        }
        
        function retryConnection() {
            document.getElementById('errorMessage').style.display = 'none';
            initializeMeeting();
        }
        
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
