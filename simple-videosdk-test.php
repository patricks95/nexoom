<?php
require_once 'includes/auth.php';
require_once 'includes/videosdk.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$meetingId = isset($_GET['room']) ? $_GET['room'] : 'test_meeting_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoSDK Test - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://sdk.videosdk.live/js-sdk/0.0.68/videosdk.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            color: #d4af37;
            padding: 25px;
            text-align: center;
            border-bottom: 3px solid #4a7c59;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }
        
        .video-container {
            flex: 1;
            background: #000;
            position: relative;
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px;
            padding: 10px;
            height: calc(100vh - 200px);
        }
        
        .video-item {
            position: relative;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            border-radius: 20px;
            overflow: hidden;
            min-height: 200px;
            border: 3px solid #4a7c59;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
        }
        
        .control-btn:hover {
            transform: scale(1.15) translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        .control-btn.muted {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border-color: #ef4444;
        }
        
        .status {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>VideoSDK Test Meeting</h1>
            <p>Meeting ID: <?php echo htmlspecialchars($meetingId); ?></p>
        </div>
        
        <div class="video-container">
            <div class="status">
                <i class="fas fa-circle text-green-500"></i> 
                <span>Connecting...</span>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <div>Starting video meeting...</div>
            </div>
            
            <div class="video-grid" id="video-grid">
                <!-- Videos will be added here -->
            </div>
            
            <div class="controls">
                <button class="control-btn" id="mic-btn" onclick="toggleMic()">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="control-btn" id="video-btn" onclick="toggleVideo()">
                    <i class="fas fa-video"></i>
                </button>
                <button class="control-btn" id="hangup-btn" onclick="hangup()">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let meeting = null;
        let isMicOn = true;
        let isVideoOn = true;
        
        // VideoSDK configuration
        const config = {
            apiKey: '0fc8e1a5-c073-407c-9bf4-153442433432',
            token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiIwZmM4ZTFhNS1jMDczLTQwN2MtOWJmNC0xNTM0NDI0MzM0MzIiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODMwMTAwMiwiZXhwIjoxNzg5ODM3MDAyfQ.fJDgzB4C2cdU9zi7i5uBHt6tGnhFzEqmLeRajtcONrM',
            meetingId: '<?php echo $meetingId; ?>',
            participantName: '<?php echo $user['full_name']; ?>',
            participantId: '<?php echo $user['id']; ?>',
            region: 'sg001',
            micEnabled: true,
            webcamEnabled: true,
            debug: true
        };
        
        // Initialize VideoSDK meeting
        async function initMeeting() {
            try {
                console.log('Initializing VideoSDK with config:', config);
                
                // Configure VideoSDK
                VideoSDK.config(config.apiKey);
                
                // Create meeting
                meeting = VideoSDK.initMeeting({
                    meetingId: config.meetingId,
                    name: config.participantName,
                    micEnabled: config.micEnabled,
                    webcamEnabled: config.webcamEnabled,
                    participantId: config.participantId,
                    region: config.region,
                    debug: config.debug
                });
                
                // Set up event listeners
                setupEventListeners();
                
                // Join meeting
                meeting.join();
                
            } catch (error) {
                console.error('Error initializing meeting:', error);
                document.getElementById('loading').innerHTML = '<div class="spinner"></div><div>Error: ' + error.message + '</div>';
            }
        }
        
        function setupEventListeners() {
            // Meeting joined
            meeting.on("meeting-joined", () => {
                console.log('Meeting joined successfully');
                document.getElementById('loading').style.display = 'none';
                document.querySelector('.status span').textContent = 'Connected';
                addLocalVideo();
            });
            
            // Participant joined
            meeting.on("participant-joined", (participant) => {
                console.log('Participant joined:', participant);
                addParticipantVideo(participant);
            });
            
            // Participant left
            meeting.on("participant-left", (participant) => {
                console.log('Participant left:', participant);
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
                document.getElementById('loading').innerHTML = '<div class="spinner"></div><div>Error: ' + error.message + '</div>';
            });
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
            video.id = 'local-video-element';
            
            const label = document.createElement('div');
            label.className = 'video-label';
            label.textContent = 'You';
            
            videoItem.appendChild(video);
            videoItem.appendChild(label);
            videoGrid.appendChild(videoItem);
            
            // Get user media
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(error => {
                    console.error('Error accessing camera:', error);
                });
        }
        
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
        
        function removeParticipantVideo(participantId) {
            const videoItem = document.getElementById(`participant-${participantId}`);
            if (videoItem) {
                videoItem.remove();
            }
        }
        
        function toggleMic() {
            if (meeting) {
                meeting.toggleMic();
                isMicOn = !isMicOn;
                const micBtn = document.getElementById('mic-btn');
                micBtn.classList.toggle('muted', !isMicOn);
                micBtn.innerHTML = isMicOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
            }
        }
        
        function toggleVideo() {
            if (meeting) {
                meeting.toggleWebcam();
                isVideoOn = !isVideoOn;
                const videoBtn = document.getElementById('video-btn');
                videoBtn.classList.toggle('muted', !isVideoOn);
                videoBtn.innerHTML = isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
            }
        }
        
        function hangup() {
            if (meeting) {
                meeting.leave();
            }
            window.location.href = 'home.php';
        }
        
        // Initialize when page loads
        window.addEventListener('load', initMeeting);
    </script>
</body>
</html>
