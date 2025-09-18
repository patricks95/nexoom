<?php
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexoom - Video Conferencing Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-4">Nexoom</h1>
            <p class="text-xl text-white opacity-90">Professional Video Conferencing Platform</p>
            <div class="mt-4">
                <span class="text-white opacity-75">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</span>
                <a href="meetings.php" class="ml-4 text-white hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-video"></i> View Meetings
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                <a href="admin.php" class="ml-4 text-white hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-cog"></i> Admin Panel
                </a>
                <?php endif; ?>
                <a href="logout.php" class="ml-4 text-white hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <!-- Meeting ID Input -->
            <div class="bg-white rounded-2xl p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Join Specific Meeting</h3>
                <form method="GET" action="videosdk-meeting.php" class="flex flex-col md:flex-row gap-4">
                    <input type="text" name="room" placeholder="Enter Meeting ID" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="hidden" name="role" value="viewer">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                        Join Meeting
                    </button>
                </form>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Broadcaster Card -->
                <div class="bg-white rounded-2xl p-8 card-hover">
                    <div class="text-center">
                        <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-video text-3xl text-blue-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Broadcaster</h2>
                        <p class="text-gray-600 mb-6">Start a meeting, share your screen, and manage participants</p>
                        <a href="videosdk-meeting.php?room=meeting_<?php echo uniqid(); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 inline-block">
                            Start Meeting
                        </a>
                    </div>
                </div>
                
                <!-- Viewer Card -->
                <div class="bg-white rounded-2xl p-8 card-hover">
                    <div class="text-center">
                        <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-users text-3xl text-green-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Viewer</h2>
                        <p class="text-gray-600 mb-6">Join a meeting as a participant and interact with others</p>
                        <a href="videosdk-meeting.php?room=demo_meeting" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 inline-block">
                            Join Meeting
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <p class="text-white opacity-75">Powered by Jitsi Meet</p>
        </div>
    </div>
</body>
</html>
