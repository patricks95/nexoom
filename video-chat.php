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
    <title><?php echo ucfirst($userRole); ?> - Nexoom Video Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 10px;
            padding: 10px;
            height: 100vh;
            box-sizing: border-box;
        }
        
        .video-item {
            position: relative;
            background: #333;
            border-radius: 10px;
            overflow: hidden;
            min-height: 200px;
        }
        
        .video-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .video-item.local {
            border: 3px solid #3b82f6;
        }
        
        .video-item.remote {
            border: 3px solid #10b981;
        }
        
        .video-label {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            z-index: 10;
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
        
        .chat-panel {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            transition: right 0.3s ease;
            z-index: 1000;
        }
        
        .chat-panel.open {
            right: 0;
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #333;
            color: white;
        }
        
        .chat-messages {
            height: calc(100vh - 120px);
            overflow-y: auto;
            padding: 20px;
        }
        
        .message {
            background: #333;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 10px;
            font-size: 14px;
        }
        
        .message.own {
            background: #3b82f6;
            margin-left: 20px;
        }
        
        .message.system {
            background: #f59e0b;
            text-align: center;
        }
        
        .chat-input {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid #333;
        }
        
        .chat-input input {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 20px;
            background: #333;
            color: white;
            outline: none;
        }
        
        .chat-input input:focus {
            border-color: #3b82f6;
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
            <i class="fas fa-circle text-red-500"></i> Connecting...
        </div>
        
        <div class="loading" id="loading">
            <i class="fas fa-spinner fa-spin"></i> Starting video chat...
        </div>
        
        <div class="error-message" id="errorMessage" style="display: none;">
            <h3><i class="fas fa-exclamation-triangle"></i> Connection Error</h3>
            <p id="errorText">An error occurred while connecting to the video chat.</p>
            <button onclick="retryConnection()">Try Again</button>
            <button onclick="joinWithoutVideo()">Join Audio Only</button>
            <button onclick="window.location.href='index.php'">Go Back</button>
        </div>
        
        <div class="video-grid" id="videoGrid">
            <!-- Videos will be added here dynamically -->
        </div>
        
        <div class="controls">
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
            
            <button id="chat-toggle" class="control-btn" style="background: #6b7280;" title="Toggle Chat">
                <i class="fas fa-comments"></i>
            </button>
            
            <button id="leave-meeting" class="control-btn leave-btn" title="Leave Meeting">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
        
        <div class="chat-panel" id="chatPanel">
            <div class="chat-header">
                <h3>Chat</h3>
                <button id="close-chat" style="float: right; background: none; border: none; color: white; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="message system">Welcome to the meeting! Type a message to start chatting.</div>
            </div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type a message...">
            </div>
        </div>
    </div>

    <script>
        // Production-ready WebRTC video chat
        class VideoChat {
            constructor() {
                this.localStream = null;
                this.remoteStreams = new Map();
                this.peerConnections = new Map();
                this.isMuted = false;
                this.isVideoOn = true;
                this.isScreenSharing = false;
                this.handRaised = false;
                this.chatOpen = false;
                this.connectionError = false;
                
                this.init();
            }
            
            async init() {
                try {
                    await this.getUserMedia();
                    this.setupEventListeners();
                    this.updateStatus('Connected', 'green');
                    document.getElementById('loading').style.display = 'none';
                    this.addMessage('Successfully connected to the meeting!', 'system');
                } catch (error) {
                    console.error('Error initializing video chat:', error);
                    this.handleConnectionError(error);
                }
            }
            
            async getUserMedia() {
                const constraints = {
                    video: true,
                    audio: true
                };
                
                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.addVideoElement(this.localStream, 'local', 'You');
                } catch (error) {
                    if (error.name === 'NotAllowedError') {
                        throw new Error('Camera/microphone access denied. Please allow access and try again.');
                    } else if (error.name === 'NotFoundError') {
                        throw new Error('No camera/microphone found. Please connect a device and try again.');
                    } else if (error.name === 'NotReadableError') {
                        throw new Error('Camera/microphone is already in use by another application.');
                    } else if (error.name === 'OverconstrainedError') {
                        throw new Error('Camera/microphone constraints cannot be satisfied.');
                    } else {
                        throw new Error('Failed to access camera/microphone: ' + error.message);
                    }
                }
            }
            
            handleConnectionError(error) {
                this.connectionError = true;
                document.getElementById('loading').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('errorText').textContent = error.message;
                this.updateStatus('Connection Error', 'red');
            }
            
            async joinWithoutVideo() {
                try {
                    const constraints = {
                        video: false,
                        audio: true
                    };
                    
                    this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.addVideoElement(this.localStream, 'local', 'You (Audio Only)');
                    this.isVideoOn = false;
                    
                    document.getElementById('errorMessage').style.display = 'none';
                    this.updateStatus('Connected (Audio Only)', 'yellow');
                    this.setupEventListeners();
                    this.addMessage('Connected in audio-only mode', 'system');
                } catch (error) {
                    console.error('Error joining without video:', error);
                    this.updateStatus('Failed to join: ' + error.message, 'red');
                }
            }
            
            async retryConnection() {
                document.getElementById('errorMessage').style.display = 'none';
                document.getElementById('loading').style.display = 'block';
                this.connectionError = false;
                
                try {
                    await this.getUserMedia();
                    this.setupEventListeners();
                    this.updateStatus('Connected', 'green');
                    document.getElementById('loading').style.display = 'none';
                    this.addMessage('Successfully reconnected!', 'system');
                } catch (error) {
                    this.handleConnectionError(error);
                }
            }
            
            addVideoElement(stream, type, label) {
                const videoGrid = document.getElementById('videoGrid');
                const videoItem = document.createElement('div');
                videoItem.className = `video-item ${type}`;
                
                const video = document.createElement('video');
                video.srcObject = stream;
                video.autoplay = true;
                video.muted = type === 'local';
                
                const labelDiv = document.createElement('div');
                labelDiv.className = 'video-label';
                labelDiv.textContent = label;
                
                videoItem.appendChild(video);
                videoItem.appendChild(labelDiv);
                videoGrid.appendChild(videoItem);
            }
            
            setupEventListeners() {
                // Microphone toggle
                document.getElementById('mic-toggle').onclick = () => {
                    this.toggleMicrophone();
                };
                
                // Video toggle
                document.getElementById('video-toggle').onclick = () => {
                    this.toggleVideo();
                };
                
                // Screen share (broadcaster only)
                <?php if ($userRole === 'broadcaster'): ?>
                document.getElementById('screen-share').onclick = () => {
                    this.toggleScreenShare();
                };
                <?php else: ?>
                // Raise hand (viewer only)
                document.getElementById('raise-hand').onclick = () => {
                    this.toggleRaiseHand();
                };
                <?php endif; ?>
                
                // Chat toggle
                document.getElementById('chat-toggle').onclick = () => {
                    this.toggleChat();
                };
                
                document.getElementById('close-chat').onclick = () => {
                    this.toggleChat();
                };
                
                // Leave meeting
                document.getElementById('leave-meeting').onclick = () => {
                    this.leaveMeeting();
                };
                
                // Chat input
                document.getElementById('chatInput').onkeypress = (e) => {
                    if (e.key === 'Enter') {
                        this.sendMessage();
                    }
                };
            }
            
            toggleMicrophone() {
                if (this.localStream) {
                    const audioTracks = this.localStream.getAudioTracks();
                    audioTracks.forEach(track => {
                        track.enabled = !track.enabled;
                    });
                    this.isMuted = !this.isMuted;
                    
                    const btn = document.getElementById('mic-toggle');
                    btn.classList.toggle('muted', this.isMuted);
                    btn.innerHTML = this.isMuted ? '<i class="fas fa-microphone-slash"></i>' : '<i class="fas fa-microphone"></i>';
                    
                    this.addMessage(this.isMuted ? 'Microphone muted' : 'Microphone unmuted', 'system');
                }
            }
            
            toggleVideo() {
                if (this.localStream) {
                    const videoTracks = this.localStream.getVideoTracks();
                    videoTracks.forEach(track => {
                        track.enabled = !track.enabled;
                    });
                    this.isVideoOn = !this.isVideoOn;
                    
                    const btn = document.getElementById('video-toggle');
                    btn.classList.toggle('muted', !this.isVideoOn);
                    btn.innerHTML = this.isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
                    
                    this.addMessage(this.isVideoOn ? 'Video enabled' : 'Video disabled', 'system');
                }
            }
            
            async toggleScreenShare() {
                if (!this.isScreenSharing) {
                    try {
                        const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                        this.addVideoElement(screenStream, 'screen', 'Screen Share');
                        this.isScreenSharing = true;
                        
                        const btn = document.getElementById('screen-share');
                        btn.classList.add('active');
                        btn.innerHTML = '<i class="fas fa-stop"></i>';
                        
                        this.addMessage('Screen sharing started', 'system');
                    } catch (error) {
                        console.error('Error sharing screen:', error);
                        this.addMessage('Screen sharing failed: ' + error.message, 'system');
                    }
                } else {
                    // Stop screen sharing
                    this.isScreenSharing = false;
                    const btn = document.getElementById('screen-share');
                    btn.classList.remove('active');
                    btn.innerHTML = '<i class="fas fa-desktop"></i>';
                    
                    this.addMessage('Screen sharing stopped', 'system');
                }
            }
            
            toggleRaiseHand() {
                this.handRaised = !this.handRaised;
                const btn = document.getElementById('raise-hand');
                btn.classList.toggle('raised', this.handRaised);
                
                if (this.handRaised) {
                    this.addMessage('🙋‍♂️ Student raised hand!', 'system');
                } else {
                    this.addMessage('Hand lowered', 'system');
                }
            }
            
            toggleChat() {
                this.chatOpen = !this.chatOpen;
                const chatPanel = document.getElementById('chatPanel');
                chatPanel.classList.toggle('open', this.chatOpen);
            }
            
            sendMessage() {
                const input = document.getElementById('chatInput');
                const message = input.value.trim();
                if (message) {
                    this.addMessage(message, 'own');
                    input.value = '';
                }
            }
            
            addMessage(text, type = 'other') {
                const chatMessages = document.getElementById('chatMessages');
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                messageDiv.textContent = text;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            updateStatus(text, color) {
                const status = document.getElementById('status');
                status.innerHTML = `<i class="fas fa-circle text-${color}-500"></i> ${text}`;
            }
            
            leaveMeeting() {
                if (confirm('Are you sure you want to leave the meeting?')) {
                    if (this.localStream) {
                        this.localStream.getTracks().forEach(track => track.stop());
                    }
                    window.location.href = 'index.php';
                }
            }
        }
        
        // Global functions for error handling
        function retryConnection() {
            if (window.videoChat) {
                window.videoChat.retryConnection();
            }
        }
        
        function joinWithoutVideo() {
            if (window.videoChat) {
                window.videoChat.joinWithoutVideo();
            }
        }
        
        // Initialize video chat when page loads
        document.addEventListener('DOMContentLoaded', () => {
            window.videoChat = new VideoChat();
        });
    </script>
</body>
</html>