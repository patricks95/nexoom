<?php
// Clean VideoSDK implementation without any problematic attributes
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'meeting_' . uniqid();
$participantName = isset($_GET['name']) && !empty(trim($_GET['name'])) ? trim($_GET['name']) : 'User_' . uniqid();

// Use the provided token directly
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcGlrZXkiOiI3MzY0ODk5My1iZWZkLTQwYzMtYmE3MS01NmEzZDFlNmUzMDQiLCJwZXJtaXNzaW9ucyI6WyJhbGxvd19qb2luIl0sImlhdCI6MTc1ODM0OTQ1NSwiZXhwIjoxNzg5ODg1NDU1fQ.6iIQeg2rABa0Mp3gfUxqsSxd6J8GBuyQ6tP7msoPuJU';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Meeting - Nexoom</title>
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
        }
        
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            color: #d4af37;
        }
        
        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 5px solid rgba(212, 175, 55, 0.3);
            border-top: 5px solid #d4af37;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 30px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }
        
        .meeting-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #000;
        }
        
        .error-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a4d3a 0%, #2d5a3d 50%, #1a3d2e 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            color: #dc2626;
            text-align: center;
            padding: 40px;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }
        
        .debug-info {
            position: fixed;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: #00ff00;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            z-index: 10000;
            max-width: 300px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="meeting-container">
        <!-- Debug Info -->
        <div class="debug-info" id="debugInfo">
            <strong>Clean VideoSDK Test:</strong><br>
            Meeting ID: <?php echo htmlspecialchars($meetingId); ?><br>
            Name: <?php echo htmlspecialchars($participantName); ?><br>
            <a href="index.php" style="color: #00ff00;">Back to Home</a>
        </div>
        
        <!-- Loading Screen -->
        <div class="loading-screen" id="loadingScreen">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading Video Meeting</div>
        </div>
        
        <!-- Error Screen -->
        <div class="error-screen" id="errorScreen" style="display: none;">
            <div style="font-size: 80px; margin-bottom: 30px;">⚠️</div>
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 20px; text-transform: uppercase;">Meeting Error</div>
            <div id="errorMessage" style="font-size: 18px; margin-bottom: 40px; max-width: 600px; line-height: 1.6;">
                There was an error loading the video meeting. Please check your internet connection and try again.
            </div>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                <button class="btn btn-primary" onclick="retryMeeting()">
                    <i class="fas fa-redo"></i> Retry
                </button>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
            </div>
        </div>
        
        <!-- VideoSDK Meeting iframe - Clean implementation -->
        <iframe 
            id="meetingIframe"
            class="meeting-iframe"
            src="https://videosdk.live/?token=<?php echo $token; ?>&meetingId=<?php echo $meetingId; ?>&name=<?php echo urlencode($participantName); ?>&micEnabled=true&webcamEnabled=true&chatEnabled=true&screenShareEnabled=true&recordingEnabled=false&liveStreamEnabled=false&whiteboardEnabled=false&raiseHandEnabled=false&participantCanToggleSelfWebcam=true&participantCanToggleSelfMic=true&participantCanLeave=true&participantCanEndMeeting=false&joinScreenEnabled=false&brandingEnabled=false&brandName=Nexoom&poweredBy=false&redirectOnLeave=index.php&layoutType=GRID&maxResolution=hd&debug=false"
            allow="camera; microphone; display-capture; autoplay"
            allowfullscreen
            style="display: none;"
        ></iframe>
    </div>
    
    <script>
        let meetingIframe = null;
        let loadingScreen = null;
        let errorScreen = null;
        
        // Initialize when page loads
        window.addEventListener('load', function() {
            meetingIframe = document.getElementById('meetingIframe');
            loadingScreen = document.getElementById('loadingScreen');
            errorScreen = document.getElementById('errorScreen');
            
            // Start the meeting
            startMeeting();
        });
        
        function startMeeting() {
            try {
                console.log('Starting clean VideoSDK meeting...');
                console.log('Meeting ID:', '<?php echo $meetingId; ?>');
                console.log('Participant Name:', '<?php echo $participantName; ?>');
                console.log('Token (first 20 chars):', '<?php echo substr($token, 0, 20); ?>...');
                
                // Show loading screen
                loadingScreen.style.display = 'flex';
                errorScreen.style.display = 'none';
                
                // Set up iframe load event
                meetingIframe.onload = function() {
                    console.log('Meeting iframe loaded successfully');
                    hideLoading();
                };
                
                // Set up iframe error event
                meetingIframe.onerror = function() {
                    console.error('Error loading meeting iframe');
                    showError('Failed to load video meeting. Please check your internet connection.');
                };
                
                // Show the iframe immediately
                meetingIframe.style.display = 'block';
                
                // Set timeout for loading
                setTimeout(function() {
                    if (loadingScreen.style.display !== 'none') {
                        showError('Meeting is taking too long to load. Please try again.');
                    }
                }, 30000); // Increased timeout to 30 seconds
                
            } catch (error) {
                console.error('Error starting meeting:', error);
                showError('Failed to start meeting: ' + error.message);
            }
        }
        
        function hideLoading() {
            if (loadingScreen) {
                loadingScreen.style.display = 'none';
            }
        }
        
        function showError(message) {
            hideLoading();
            if (errorScreen) {
                document.getElementById('errorMessage').textContent = message;
                errorScreen.style.display = 'flex';
            }
        }
        
        function retryMeeting() {
            console.log('Retrying meeting...');
            errorScreen.style.display = 'none';
            loadingScreen.style.display = 'flex';
            meetingIframe.style.display = 'none';
            
            // Reload the iframe
            setTimeout(function() {
                meetingIframe.src = meetingIframe.src;
                meetingIframe.style.display = 'block';
            }, 1000);
        }
        
        // Handle page unload
        window.addEventListener('beforeunload', function() {
            console.log('Leaving meeting...');
        });
    </script>
</body>
</html>
