<?php
// Modern Dashboard Component for Nexoom Video Conferencing Platform

function renderDashboard($user, $meetings = [], $stats = []) {
    $userName = htmlspecialchars($user['full_name']);
    $userRole = htmlspecialchars($user['role']);
    $userInitial = strtoupper(substr($userName, 0, 1));
    
    // Default stats if not provided
    $totalMeetings = $stats['total_meetings'] ?? 0;
    $activeMeetings = $stats['active_meetings'] ?? 0;
    $totalParticipants = $stats['total_participants'] ?? 0;
    $totalDuration = $stats['total_duration'] ?? '0h 0m';
    
    return "
    <div class='dashboard-container'>
        <!-- Header Section -->
        <div class='dashboard-header'>
            <div class='header-content'>
                <div class='logo-section'>
                    <h1 class='logo'>Nexoom</h1>
                    <span class='tagline'>Professional Video Conferencing</span>
                </div>
                <div class='user-section'>
                    <div class='user-avatar'>
                        <span class='avatar-text'>{$userInitial}</span>
                    </div>
                    <div class='user-info'>
                        <h3 class='user-name'>{$userName}</h3>
                        <span class='user-role'>{$userRole}</span>
                    </div>
                    <a href='logout.php' class='btn btn-danger'>
                        <i class='fas fa-sign-out-alt'></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class='stats-section'>
            <div class='stat-card'>
                <div class='stat-icon'>
                    <i class='fas fa-video'></i>
                </div>
                <div class='stat-content'>
                    <h3 class='stat-number'>{$totalMeetings}</h3>
                    <p class='stat-label'>Total Meetings</p>
                </div>
            </div>
            
            <div class='stat-card'>
                <div class='stat-icon'>
                    <i class='fas fa-play-circle'></i>
                </div>
                <div class='stat-content'>
                    <h3 class='stat-number'>{$activeMeetings}</h3>
                    <p class='stat-label'>Active Now</p>
                </div>
            </div>
            
            <div class='stat-card'>
                <div class='stat-icon'>
                    <i class='fas fa-users'></i>
                </div>
                <div class='stat-content'>
                    <h3 class='stat-number'>{$totalParticipants}</h3>
                    <p class='stat-label'>Participants</p>
                </div>
            </div>
            
            <div class='stat-card'>
                <div class='stat-icon'>
                    <i class='fas fa-clock'></i>
                </div>
                <div class='stat-content'>
                    <h3 class='stat-number'>{$totalDuration}</h3>
                    <p class='stat-label'>Total Duration</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class='quick-actions'>
            <div class='action-grid'>
                <a href='videosdk-meeting.php?room=meeting_" . uniqid() . "' class='action-card start-meeting'>
                    <div class='action-icon'>
                        <i class='fas fa-video'></i>
                    </div>
                    <h3 class='action-title'>Start Meeting</h3>
                    <p class='action-description'>Create a new video conference instantly</p>
                </a>
                
                <a href='videosdk-meeting.php?room=demo_meeting' class='action-card join-meeting'>
                    <div class='action-icon'>
                        <i class='fas fa-users'></i>
                    </div>
                    <h3 class='action-title'>Join Meeting</h3>
                    <p class='action-description'>Enter meeting ID to join existing call</p>
                </a>
                
                <a href='simple-videosdk-test.php?room=test_" . uniqid() . "' class='action-card test-meeting'>
                    <div class='action-icon'>
                        <i class='fas fa-flask'></i>
                    </div>
                    <h3 class='action-title'>Test Meeting</h3>
                    <p class='action-description'>Test your video and audio setup</p>
                </a>
                
                " . ($userRole === 'admin' ? "
                <a href='admin.php' class='action-card admin-panel'>
                    <div class='action-icon'>
                        <i class='fas fa-cog'></i>
                    </div>
                    <h3 class='action-title'>Admin Panel</h3>
                    <p class='action-description'>Manage users and system settings</p>
                </a>
                " : "") . "
            </div>
        </div>

        <!-- Recent Meetings -->
        <div class='recent-meetings'>
            <h2 class='section-title'>Recent Meetings</h2>
            <div class='meetings-list'>
                " . renderMeetingsList($meetings) . "
            </div>
        </div>

        <!-- Meeting Form -->
        <div class='meeting-form-section'>
            <div class='form-card'>
                <h3 class='form-title'>Join Specific Meeting</h3>
                <form method='GET' action='videosdk-meeting.php' class='meeting-form'>
                    <div class='form-group'>
                        <input type='text' name='room' placeholder='Enter Meeting ID' class='input' required>
                        <button type='submit' class='btn btn-danger'>
                            <i class='fas fa-arrow-right'></i> Join
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .dashboard-container {
            min-height: 100vh;
            background: var(--gradient-background);
            padding: var(--space-6);
        }

        .dashboard-header {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            margin-bottom: var(--space-8);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-xl);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo-section .logo {
            font-size: var(--font-size-4xl);
            font-weight: 800;
            color: var(--primary-yellow);
            text-shadow: 0 2px 10px rgba(212, 175, 55, 0.3);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: var(--space-2);
        }

        .tagline {
            color: var(--white);
            font-size: var(--font-size-lg);
            opacity: 0.9;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: var(--space-6);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary-dark-green);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-lg);
            font-size: var(--font-size-xl);
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--primary-yellow);
            margin-bottom: var(--space-1);
        }

        .user-role {
            color: var(--white);
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: var(--font-size-sm);
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }

        .stat-card {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            gap: var(--space-6);
            transition: var(--transition-bounce);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-yellow);
            box-shadow: var(--shadow-glow);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-2xl);
            color: var(--primary-dark-green);
            box-shadow: var(--shadow-lg);
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: var(--font-size-3xl);
            font-weight: 800;
            color: var(--primary-yellow);
            margin-bottom: var(--space-2);
        }

        .stat-label {
            color: var(--white);
            font-size: var(--font-size-lg);
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .quick-actions {
            margin-bottom: var(--space-8);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-6);
        }

        .action-card {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-xl);
            text-decoration: none;
            color: inherit;
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(212, 175, 55, 0.05));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-2xl);
            border-color: var(--primary-yellow);
        }

        .action-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-3xl);
            margin-bottom: var(--space-6);
            border: 4px solid var(--primary-light-green);
            box-shadow: var(--shadow-lg);
        }

        .start-meeting .action-icon {
            background: var(--gradient-secondary);
            color: var(--primary-dark-green);
        }

        .join-meeting .action-icon {
            background: var(--gradient-primary);
            color: var(--primary-yellow);
        }

        .test-meeting .action-icon {
            background: var(--gradient-danger);
            color: var(--white);
        }

        .admin-panel .action-icon {
            background: var(--gradient-danger);
            color: var(--white);
        }

        .action-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--primary-yellow);
            margin-bottom: var(--space-4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .action-description {
            color: var(--white);
            font-size: var(--font-size-lg);
            opacity: 0.9;
            line-height: 1.6;
        }

        .recent-meetings {
            margin-bottom: var(--space-8);
        }

        .section-title {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--primary-yellow);
            margin-bottom: var(--space-6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meetings-list {
            display: grid;
            gap: var(--space-4);
        }

        .meeting-item {
            background: var(--gradient-primary);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: var(--space-4);
            transition: var(--transition-normal);
        }

        .meeting-item:hover {
            border-color: var(--primary-yellow);
            transform: translateY(-2px);
        }

        .meeting-form-section {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            border: 3px solid var(--primary-light-green);
            box-shadow: var(--shadow-xl);
        }

        .form-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--primary-yellow);
            margin-bottom: var(--space-6);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meeting-form .form-group {
            display: flex;
            gap: var(--space-4);
        }

        .meeting-form .input {
            flex: 1;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: var(--space-6);
                text-align: center;
            }

            .user-section {
                flex-direction: column;
                gap: var(--space-4);
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .meeting-form .form-group {
                flex-direction: column;
            }
        }
    </style>
    ";
}

function renderMeetingsList($meetings) {
    if (empty($meetings)) {
        return "
        <div class='empty-state'>
            <i class='fas fa-video' style='font-size: 3rem; color: var(--primary-yellow); margin-bottom: 1rem;'></i>
            <h3 style='color: var(--primary-yellow); margin-bottom: 0.5rem;'>No Recent Meetings</h3>
            <p style='color: var(--white); opacity: 0.8;'>Start your first meeting to see it here</p>
        </div>
        ";
    }

    $html = '';
    foreach ($meetings as $meeting) {
        $meetingId = htmlspecialchars($meeting['id']);
        $meetingName = htmlspecialchars($meeting['name']);
        $createdAt = date('M j, Y g:i A', strtotime($meeting['created_at']));
        $participantCount = $meeting['participant_count'] ?? 0;
        
        $html .= "
        <div class='meeting-item'>
            <div class='meeting-icon'>
                <i class='fas fa-video'></i>
            </div>
            <div class='meeting-info'>
                <h4 class='meeting-name'>{$meetingName}</h4>
                <p class='meeting-details'>
                    <span class='meeting-date'>{$createdAt}</span>
                    <span class='meeting-participants'>{$participantCount} participants</span>
                </p>
            </div>
            <div class='meeting-actions'>
                <a href='videosdk-meeting.php?room={$meetingId}' class='btn btn-primary btn-sm'>
                    <i class='fas fa-play'></i> Join
                </a>
            </div>
        </div>
        ";
    }
    
    return $html;
}
?>
