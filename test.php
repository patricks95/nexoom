<?php
// Test page to debug the system
require_once 'includes/auth.php';
require_once 'includes/meeting.php';

echo "<h1>Nexoom System Test</h1>";

// Test database connection
try {
    $db = getDB();
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</p>";
}

// Test authentication
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p style='color: green;'>✅ User logged in: " . $user['full_name'] . " (" . $user['role'] . ")</p>";
} else {
    echo "<p style='color: orange;'>⚠️ User not logged in</p>";
}

// Test meeting system
try {
    $meeting = new Meeting();
    echo "<p style='color: green;'>✅ Meeting class: SUCCESS</p>";
    
    // Test getting a meeting
    $testMeeting = $meeting->getMeeting('nexoom_demo');
    if ($testMeeting) {
        echo "<p style='color: green;'>✅ Test meeting found: " . $testMeeting['room_name'] . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Test meeting not found (this is normal if not created yet)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Meeting system: FAILED - " . $e->getMessage() . "</p>";
}

// Test user permissions
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<h2>User Permissions:</h2>";
    echo "<p>Can create meeting: " . (hasPermission('create_meeting') ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p>Can join meeting: " . (hasPermission('join_meeting') ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p>Can manage users: " . (hasPermission('manage_users') ? '✅ YES' : '❌ NO') . "</p>";
}

echo "<h2>Quick Actions:</h2>";
echo "<p><a href='login.php'>Go to Login</a></p>";
echo "<p><a href='index.php'>Go to Dashboard</a></p>";

// Test meeting creation
if (isLoggedIn() && hasPermission('create_meeting')) {
    echo "<h2>Test Meeting Creation:</h2>";
    $testRoomId = 'test_' . time();
    $result = $meeting->createMeeting(
        $testRoomId,
        'Test Meeting',
        'Test meeting created by system test',
        getCurrentUser()['id'],
        'public',
        10,
        null
    );
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Test meeting created: " . $testRoomId . "</p>";
        echo "<p><a href='jitsi-meeting.php?room=" . $testRoomId . "'>Join Test Meeting</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Test meeting creation failed: " . $result['message'] . "</p>";
    }
}
?>
