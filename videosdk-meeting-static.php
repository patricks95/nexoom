<?php
require_once 'includes/videosdk.php';

// Get parameters from URL or use defaults
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : 'meeting_' . uniqid();
$participantName = isset($_GET['name']) ? $_GET['name'] : 'Participant';
$participantId = isset($_GET['participantId']) ? $_GET['participantId'] : uniqid();

// Initialize VideoSDK
$videoSDK = new VideoSDK('73648993-befd-40c3-ba71-56a3d1e6e304', 'f604fad113af89b72ae9df09d9ca9a4bfa83a36b0e28543668ab947f0e40b02e');

// Get frontend configuration
$config = $videoSDK->getFrontendConfig($meetingId, $participantName, $participantId);

// URL parameters for VideoSDK
$urlParams = [
    'token' => $config['token'],
    'meetingId' => $meetingId,
    'name' => $participantName,
    'participantId' => $participantId,
    'region' => $config['region'],
    'micEnabled' => 'true',
    'webcamEnabled' => 'true',
    'chatEnabled' => 'true',
    'screenShareEnabled' => 'true',
    'recordingEnabled' => 'false',
    'liveStreamEnabled' => 'false',
    'whiteboardEnabled' => 'false',
    'raiseHandEnabled' => 'false',
    'participantCanToggleSelfWebcam' => 'true',
    'participantCanToggleSelfMic' => 'true',
    'participantCanLeave' => 'true',
    'participantCanEndMeeting' => 'false',
    'joinScreenEnabled' => 'true',
    'joinScreenMeetingUrl' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
    'joinScreenTitle' => 'Nexoom Video Meeting',
    'brandingEnabled' => 'false',
    'brandName' => 'Nexoom',
    'poweredBy' => 'false',
    'redirectOnLeave' => 'index.php',
    'layoutType' => 'GRID',
    'maxResolution' => 'hd',
    'debug' => 'false'
];

// Build URL with parameters
$videoSDKUrl = 'https://videosdk.live/?' . http_build_query($urlParams);

// Output the URL as a JavaScript variable
echo '<script>var meetingUrl = "' . addslashes($videoSDKUrl) . '";</script>';
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
    </style>
</head>
<body>
    <div class="meeting-container">
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
        
        <!-- VideoSDK Meeting iframe -->
        <iframe 
            id="meetingIframe"
            class="meeting-iframe"
            src="<?php echo $videoSDKUrl; ?>"
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
                console.log('Starting VideoSDK meeting...');
                console.log('Meeting URL:', meetingUrl);
                
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
                }, 10000);
                
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
                meetingIframe.src = meetingUrl;
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
