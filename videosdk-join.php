<?php
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : '';
$participantName = $user['full_name'];
$participantId = $user['id'];

// Get URL parameters for VideoSDK
$micEnabled = isset($_GET['micEnabled']) ? $_GET['micEnabled'] : 'true';
$webcamEnabled = isset($_GET['webcamEnabled']) ? $_GET['webcamEnabled'] : 'true';
$chatEnabled = isset($_GET['chatEnabled']) ? $_GET['chatEnabled'] : 'true';
$screenShareEnabled = isset($_GET['screenShareEnabled']) ? $_GET['screenShareEnabled'] : 'true';
$recordingEnabled = isset($_GET['recordingEnabled']) ? $_GET['recordingEnabled'] : 'false';
$liveStreamEnabled = isset($_GET['liveStreamEnabled']) ? $_GET['liveStreamEnabled'] : 'false';
$whiteboardEnabled = isset($_GET['whiteboardEnabled']) ? $_GET['whiteboardEnabled'] : 'false';
$raiseHandEnabled = isset($_GET['raiseHandEnabled']) ? $_GET['raiseHandEnabled'] : 'false';
$brandingEnabled = isset($_GET['brandingEnabled']) ? $_GET['brandingEnabled'] : 'false';
$layoutType = isset($_GET['layoutType']) ? $_GET['layoutType'] : 'GRID';
$maxResolution = isset($_GET['maxResolution']) ? $_GET['maxResolution'] : 'hd';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meeting - Nexoom</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .join-container {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 30px;
            padding: 60px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 3px solid #4a7c59;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }
        
        .logo {
            font-size: 48px;
            font-weight: 800;
            color: #d4af37;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .join-title {
            font-size: 32px;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .join-subtitle {
            font-size: 18px;
            color: #a0a0a0;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 30px;
            text-align: left;
        }
        
        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #d4af37;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-input {
            width: 100%;
            padding: 18px 25px;
            border: 3px solid #4a7c59;
            border-radius: 15px;
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
            transform: translateY(-2px);
        }
        
        .form-input::placeholder {
            color: #a0a0a0;
        }
        
        .settings-section {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid #4a7c59;
        }
        
        .settings-title {
            font-size: 18px;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .setting-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            border-radius: 10px;
            border: 2px solid #4a7c59;
            transition: all 0.3s ease;
        }
        
        .setting-item:hover {
            border-color: #d4af37;
            transform: translateY(-2px);
        }
        
        .setting-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #d4af37;
        }
        
        .setting-label {
            color: #d4af37;
            font-weight: 600;
            font-size: 14px;
        }
        
        .btn {
            padding: 18px 40px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: inline-block;
            margin: 10px;
            min-width: 200px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            border: 3px solid #4a7c59;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        .error-message {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 600;
            display: none;
        }
        
        .success-message {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 600;
            display: none;
            border: 2px solid #4a7c59;
        }
        
        .meeting-id-display {
            background: linear-gradient(135deg, #2d5a3d, #1a4d3a);
            color: #d4af37;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 2px solid #4a7c59;
            word-break: break-all;
        }
        
        .copy-meeting-id {
            background: linear-gradient(135deg, #d4af37, #b8941f);
            color: #1a3d2e;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .join-container {
                padding: 40px 30px;
                margin: 10px;
            }
            
            .logo {
                font-size: 36px;
            }
            
            .join-title {
                font-size: 24px;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="join-container">
        <div class="logo">Nexoom</div>
        <h1 class="join-title">Join Video Meeting</h1>
        <p class="join-subtitle">Enter meeting details and configure your settings to join the video conference.</p>
        
        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>
        
        <form id="joinForm" method="GET" action="videosdk-meeting.php">
            <div class="form-group">
                <label class="form-label" for="meetingId">Meeting ID</label>
                <input 
                    type="text" 
                    id="meetingId" 
                    name="meetingId" 
                    class="form-input" 
                    placeholder="Enter Meeting ID" 
                    value="<?php echo htmlspecialchars($meetingId); ?>"
                    required
                >
                <?php if ($meetingId): ?>
                <div class="meeting-id-display">
                    <strong>Current Meeting ID:</strong> <?php echo htmlspecialchars($meetingId); ?>
                    <button type="button" class="copy-meeting-id" onclick="copyMeetingId()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="settings-section">
                <h3 class="settings-title">Meeting Settings</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <input type="checkbox" id="micEnabled" name="micEnabled" class="setting-checkbox" value="true" <?php echo $micEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="micEnabled" class="setting-label">Microphone Enabled</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="webcamEnabled" name="webcamEnabled" class="setting-checkbox" value="true" <?php echo $webcamEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="webcamEnabled" class="setting-label">Camera Enabled</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="chatEnabled" name="chatEnabled" class="setting-checkbox" value="true" <?php echo $chatEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="chatEnabled" class="setting-label">Chat Enabled</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="screenShareEnabled" name="screenShareEnabled" class="setting-checkbox" value="true" <?php echo $screenShareEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="screenShareEnabled" class="setting-label">Screen Share</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="recordingEnabled" name="recordingEnabled" class="setting-checkbox" value="true" <?php echo $recordingEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="recordingEnabled" class="setting-label">Recording</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="liveStreamEnabled" name="liveStreamEnabled" class="setting-checkbox" value="true" <?php echo $liveStreamEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="liveStreamEnabled" class="setting-label">Live Stream</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="whiteboardEnabled" name="whiteboardEnabled" class="setting-checkbox" value="true" <?php echo $whiteboardEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="whiteboardEnabled" class="setting-label">Whiteboard</label>
                    </div>
                    <div class="setting-item">
                        <input type="checkbox" id="raiseHandEnabled" name="raiseHandEnabled" class="setting-checkbox" value="true" <?php echo $raiseHandEnabled === 'true' ? 'checked' : ''; ?>>
                        <label for="raiseHandEnabled" class="setting-label">Raise Hand</label>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h3 class="settings-title">Advanced Settings</h3>
                <div class="form-group">
                    <label class="form-label" for="layoutType">Layout Type</label>
                    <select id="layoutType" name="layoutType" class="form-input">
                        <option value="GRID" <?php echo $layoutType === 'GRID' ? 'selected' : ''; ?>>Grid Layout</option>
                        <option value="SPOTLIGHT" <?php echo $layoutType === 'SPOTLIGHT' ? 'selected' : ''; ?>>Spotlight Layout</option>
                        <option value="SIDEBAR" <?php echo $layoutType === 'SIDEBAR' ? 'selected' : ''; ?>>Sidebar Layout</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="maxResolution">Max Resolution</label>
                    <select id="maxResolution" name="maxResolution" class="form-input">
                        <option value="sd" <?php echo $maxResolution === 'sd' ? 'selected' : ''; ?>>SD (480p)</option>
                        <option value="hd" <?php echo $maxResolution === 'hd' ? 'selected' : ''; ?>>HD (720p)</option>
                        <option value="fhd" <?php echo $maxResolution === 'fhd' ? 'selected' : ''; ?>>FHD (1080p)</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-video"></i> Join Meeting
                </button>
                <a href="home.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </form>
    </div>
    
    <script>
        function copyMeetingId() {
            const meetingId = document.getElementById('meetingId').value;
            if (meetingId) {
                navigator.clipboard.writeText(meetingId).then(() => {
                    showSuccess('Meeting ID copied to clipboard!');
                }).catch(err => {
                    showError('Failed to copy meeting ID');
                });
            } else {
                showError('Please enter a meeting ID first');
            }
        }
        
        function showError(message) {
            const errorEl = document.getElementById('errorMessage');
            const successEl = document.getElementById('successMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            successEl.style.display = 'none';
        }
        
        function showSuccess(message) {
            const errorEl = document.getElementById('errorMessage');
            const successEl = document.getElementById('successMessage');
            successEl.textContent = message;
            successEl.style.display = 'block';
            errorEl.style.display = 'none';
        }
        
        // Handle form submission
        document.getElementById('joinForm').addEventListener('submit', function(e) {
            const meetingId = document.getElementById('meetingId').value.trim();
            if (!meetingId) {
                e.preventDefault();
                showError('Please enter a meeting ID');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Joining...';
            submitBtn.disabled = true;
        });
        
        // Auto-generate meeting ID if not provided
        document.getElementById('meetingId').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'meeting_' + Math.random().toString(36).substr(2, 9);
            }
        });
    </script>
</body>
</html>
