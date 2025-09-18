<?php
require_once 'includes/auth.php';
require_once 'includes/meeting.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$meeting = new Meeting();

// Get all active meetings
$activeMeetings = $meeting->getActiveMeetings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings - Nexoom</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-white">Active Meetings</h1>
            <div class="flex space-x-4">
                <a href="index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="grid gap-6">
            <?php if (empty($activeMeetings)): ?>
            <div class="bg-white rounded-2xl p-8 text-center">
                <i class="fas fa-video text-6xl text-gray-400 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Active Meetings</h3>
                <p class="text-gray-600 mb-6">There are currently no active meetings. Start a new meeting to get started!</p>
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 inline-block">
                    Start New Meeting
                </a>
            </div>
            <?php else: ?>
                <?php foreach ($activeMeetings as $meetingData): ?>
                <div class="bg-white rounded-2xl p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($meetingData['room_name']); ?></h3>
                            <p class="text-gray-600 mb-2">Meeting ID: <code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($meetingData['room_id']); ?></code></p>
                            <p class="text-gray-600 mb-2">Created by: <?php echo htmlspecialchars($meetingData['created_by_name']); ?></p>
                            <p class="text-gray-600 mb-2">Participants: <?php echo $meetingData['current_participants']; ?>/<?php echo $meetingData['max_participants']; ?></p>
                            <p class="text-gray-600 mb-4">Type: <?php echo ucfirst($meetingData['meeting_type']); ?></p>
                            
                            <?php if (!empty($meetingData['description'])): ?>
                            <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($meetingData['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="simple-meeting.php?room=<?php echo urlencode($meetingData['room_id']); ?>" 
                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-300">
                                <i class="fas fa-video mr-2"></i> Join Meeting
                            </a>
                            
                            <?php if ($meetingData['created_by'] == $user['id'] || $user['role'] == 'admin'): ?>
                            <button onclick="endMeeting('<?php echo $meetingData['room_id']; ?>')" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-300">
                                <i class="fas fa-stop mr-2"></i> End Meeting
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-white opacity-75">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>! (<?php echo ucfirst($user['role']); ?>)</p>
        </div>
    </div>
    
    <script>
        function endMeeting(roomId) {
            if (confirm('Are you sure you want to end this meeting? All participants will be disconnected.')) {
                // In a real implementation, you would call an API to end the meeting
                alert('Meeting ended. In a full implementation, this would disconnect all participants.');
            }
        }
    </script>
</body>
</html>
