<?php
require_once 'includes/auth.php';
require_once 'includes/meeting.php';

// Require admin role
requireRole(['admin']);

$user = getCurrentUser();
$meeting = new Meeting();

// Get all active meetings
$activeMeetings = $meeting->getActiveMeetings();

// Get all users (simplified - in production you'd paginate this)
$allUsers = fetchAll("SELECT id, username, email, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nexoom</title>
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
            <h1 class="text-4xl font-bold text-white">Admin Dashboard</h1>
            <div class="flex space-x-4">
                <a href="index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="meetings.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-video mr-2"></i> Meetings
                </a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($allUsers); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-video text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Meetings</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($activeMeetings); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-user-tie text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Broadcasters</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($allUsers, function($u) { return $u['role'] === 'broadcaster'; })); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="flex flex-wrap gap-4">
                <a href="simple-meeting.php?room=admin_meeting_<?php echo uniqid(); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-300">
                    <i class="fas fa-plus mr-2"></i> Create Meeting
                </a>
                <a href="test.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-300">
                    <i class="fas fa-cog mr-2"></i> System Test
                </a>
                <button onclick="refreshData()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition duration-300">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Data
                </button>
            </div>
        </div>
        
        <!-- Active Meetings -->
        <div class="bg-white rounded-2xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Active Meetings</h2>
            <?php if (empty($activeMeetings)): ?>
            <p class="text-gray-600">No active meetings found.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meeting</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($activeMeetings as $meetingData): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($meetingData['room_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($meetingData['room_id']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($meetingData['created_by_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $meetingData['current_participants']; ?>/<?php echo $meetingData['max_participants']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo ucfirst($meetingData['meeting_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="simple-meeting.php?room=<?php echo urlencode($meetingData['room_id']); ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-video mr-1"></i> Join
                                </a>
                                <button onclick="endMeeting('<?php echo $meetingData['room_id']; ?>')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-stop mr-1"></i> End
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Users -->
        <div class="bg-white rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Users</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allUsers as $userData): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($userData['full_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($userData['email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($userData['role']) {
                                        case 'admin': echo 'bg-red-100 text-red-800'; break;
                                        case 'broadcaster': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'viewer': echo 'bg-green-100 text-green-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($userData['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $userData['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $userData['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M j, Y', strtotime($userData['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function endMeeting(roomId) {
            if (confirm('Are you sure you want to end this meeting? All participants will be disconnected.')) {
                // In a real implementation, you would call an API to end the meeting
                alert('Meeting ended. In a full implementation, this would disconnect all participants.');
                location.reload();
            }
        }
        
        function refreshData() {
            location.reload();
        }
    </script>
</body>
</html>
