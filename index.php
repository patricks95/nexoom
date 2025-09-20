<?php
// Simple home page without login requirement
$meetingId = isset($_GET['meetingId']) ? $_GET['meetingId'] : '';
$participantName = isset($_GET['name']) ? $_GET['name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexoom Video Meeting</title>
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
        
        .container {
            background: linear-gradient(135deg, #1a3d2e, #2d5a3d);
            backdrop-filter: blur(25px);
            border-radius: 30px;
            padding: 60px;
            max-width: 600px;
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
        
        .title {
            font-size: 32px;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #a0a0a0;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #d4af37;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
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
        
        .quick-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
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
        
        @media (max-width: 768px) {
            .container {
                padding: 40px 30px;
                margin: 10px;
            }
            
            .logo {
                font-size: 36px;
            }
            
            .title {
                font-size: 24px;
            }
            
            .quick-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Nexoom</div>
        <h1 class="title">Video Meeting Platform</h1>
        <p class="subtitle">Start or join video meetings instantly. No registration required.</p>
        
        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>
        
        <form id="meetingForm" method="GET" action="videosdk-fixed.php">
            <div class="form-group">
                <label class="form-label" for="meetingId">Meeting ID</label>
                <input 
                    type="text" 
                    id="meetingId" 
                    name="meetingId" 
                    class="form-input" 
                    placeholder="Enter Meeting ID (leave empty for new meeting)"
                    value="<?php echo htmlspecialchars($meetingId); ?>"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="name">Your Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-input" 
                    placeholder="Enter your name"
                    value="<?php echo htmlspecialchars($participantName); ?>"
                    required
                >
            </div>
            
            <div class="quick-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-video"></i> Join Meeting
                </button>
                <button type="button" class="btn btn-secondary" onclick="startNewMeeting()">
                    <i class="fas fa-plus"></i> Start New Meeting
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function startNewMeeting() {
            // Generate a new meeting ID
            const meetingId = 'meeting_' + Math.random().toString(36).substr(2, 9);
            document.getElementById('meetingId').value = meetingId;
            
            // Submit the form
            document.getElementById('meetingForm').submit();
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
        
        // Auto-generate meeting ID if not provided
        document.getElementById('meetingId').addEventListener('focus', function() {
            if (!this.value) {
                this.value = 'meeting_' + Math.random().toString(36).substr(2, 9);
            }
        });
        
        // Handle form submission
        document.getElementById('meetingForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            if (!name) {
                e.preventDefault();
                showError('Please enter your name');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Joining...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>