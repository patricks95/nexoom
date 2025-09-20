<?php
/**
 * VideoSDK Configuration
 * Replicates all features from the React prebuilt UI
 * Based on: https://github.com/videosdk-live/videosdk-rtc-react-prebuilt-ui
 */

class VideoSDKConfig {
    
    // Default configuration values (matching React version)
    private static $defaultConfig = [
        // Core settings
        'micEnabled' => false,
        'webcamEnabled' => false,
        'name' => '',
        'meetingId' => '',
        'region' => 'sg001',
        'redirectOnLeave' => '',
        
        // Feature toggles
        'chatEnabled' => false,
        'screenShareEnabled' => false,
        'pollEnabled' => false,
        'whiteboardEnabled' => false,
        'raiseHandEnabled' => false,
        
        // Participant permissions
        'participantCanToggleSelfWebcam' => false,
        'participantCanToggleSelfMic' => false,
        'participantCanToggleRecording' => false,
        'participantCanLeave' => true,
        'participantCanToggleOtherWebcam' => null,
        'participantCanToggleOtherMic' => null,
        'participantCanToggleLivestream' => 'todo',
        'participantCanEndMeeting' => false,
        'participantCanToggleRealtimeTranscription' => false,
        
        // Transcription settings
        'realtimeTranscriptionEnabled' => false,
        'realtimeTranscriptionVisible' => false,
        
        // Recording settings
        'recordingEnabled' => false,
        'recordingWebhookUrl' => '',
        'recordingAWSDirPath' => '',
        'autoStartRecording' => false,
        
        // Branding settings
        'brandingEnabled' => false,
        'brandLogoURL' => '',
        'brandName' => '',
        'poweredBy' => false,
        
        // Live streaming settings
        'liveStreamEnabled' => false,
        'autoStartLiveStream' => false,
        'liveStreamOutputs' => '',
        
        // Join screen settings
        'askJoin' => false,
        'joinScreenEnabled' => true,
        'joinScreenMeetingUrl' => false,
        'joinScreenTitle' => false,
        
        // Notification settings
        'notificationSoundEnabled' => false,
        
        // Participant management
        'canPin' => false,
        'canRemoveOtherParticipant' => false,
        'canDrawOnWhiteboard' => false,
        'canToggleWhiteboard' => false,
        
        // UI customization
        'leftScreenActionButtonLabel' => '',
        'leftScreenActionButtonHref' => '',
        'leftScreenRejoinButtonEnabled' => null,
        'maxResolution' => 'sd',
        'animationsEnabled' => true,
        'topbarEnabled' => true,
        'notificationAlertsEnabled' => true,
        'debug' => false,
        
        // Layout settings
        'participantId' => '',
        'layoutType' => 'GRID',
        'layoutGridSize' => null,
        'layoutPriority' => 'SPEAKER',
        'meetingLayoutTopic' => 'MEETING_LAYOUT',
        'isRecorder' => false,
        'hideLocalParticipant' => false,
        'alwaysShowOverlay' => false,
        'sideStackSize' => null,
        'reduceEdgeSpacing' => false,
        'joinWithoutUserInteraction' => false,
        'rawUserAgent' => '',
        'canChangeLayout' => false,
        'preferredProtocol' => 'UDP_ONLY'
    ];
    
    /**
     * Get configuration with URL parameters
     */
    public static function getConfig($params = []) {
        $config = self::$defaultConfig;
        
        // Override with provided parameters
        foreach ($params as $key => $value) {
            if (array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }
        
        return $config;
    }
    
    /**
     * Build VideoSDK URL with all parameters
     */
    public static function buildVideoSDKUrl($baseUrl, $config) {
        $urlParams = [];
        
        foreach ($config as $key => $value) {
            if ($value !== null && $value !== '') {
                $urlParams[$key] = $value;
            }
        }
        
        return $baseUrl . '?' . http_build_query($urlParams);
    }
    
    /**
     * Get available layout types
     */
    public static function getLayoutTypes() {
        return [
            'GRID' => 'Grid Layout',
            'SPOTLIGHT' => 'Spotlight Layout',
            'SIDEBAR' => 'Sidebar Layout'
        ];
    }
    
    /**
     * Get available resolutions
     */
    public static function getResolutions() {
        return [
            'sd' => 'SD (480p)',
            'hd' => 'HD (720p)',
            'fhd' => 'FHD (1080p)'
        ];
    }
    
    /**
     * Get available regions
     */
    public static function getRegions() {
        return [
            'sg001' => 'Singapore',
            'us001' => 'United States',
            'in001' => 'India',
            'eu001' => 'Europe',
            'au001' => 'Australia'
        ];
    }
    
    /**
     * Validate configuration
     */
    public static function validateConfig($config) {
        $errors = [];
        
        // Required fields
        if (empty($config['meetingId'])) {
            $errors[] = 'Meeting ID is required';
        }
        
        if (empty($config['name'])) {
            $errors[] = 'Participant name is required';
        }
        
        // Validate layout type
        $validLayouts = array_keys(self::getLayoutTypes());
        if (!in_array($config['layoutType'], $validLayouts)) {
            $errors[] = 'Invalid layout type';
        }
        
        // Validate resolution
        $validResolutions = array_keys(self::getResolutions());
        if (!in_array($config['maxResolution'], $validResolutions)) {
            $errors[] = 'Invalid resolution';
        }
        
        // Validate region
        $validRegions = array_keys(self::getRegions());
        if (!in_array($config['region'], $validRegions)) {
            $errors[] = 'Invalid region';
        }
        
        return $errors;
    }
    
    /**
     * Get feature descriptions (for UI help)
     */
    public static function getFeatureDescriptions() {
        return [
            'micEnabled' => 'Enable microphone by default when joining',
            'webcamEnabled' => 'Enable camera by default when joining',
            'chatEnabled' => 'Show chat panel in the meeting',
            'screenShareEnabled' => 'Allow participants to share their screen',
            'recordingEnabled' => 'Allow meeting recording',
            'liveStreamEnabled' => 'Enable live streaming to social media',
            'whiteboardEnabled' => 'Show whiteboard button in meeting',
            'raiseHandEnabled' => 'Allow participants to raise hand',
            'participantCanToggleSelfWebcam' => 'Participants can toggle their own camera',
            'participantCanToggleSelfMic' => 'Participants can toggle their own microphone',
            'participantCanLeave' => 'Participants can leave the meeting',
            'participantCanEndMeeting' => 'Participants can end the meeting for everyone',
            'brandingEnabled' => 'Show custom branding in the meeting',
            'joinScreenEnabled' => 'Show join screen before entering meeting',
            'notificationSoundEnabled' => 'Play notification sounds',
            'canPin' => 'Allow participants to pin other participants',
            'canRemoveOtherParticipant' => 'Allow removing other participants',
            'canDrawOnWhiteboard' => 'Allow drawing on whiteboard',
            'canToggleWhiteboard' => 'Allow toggling whiteboard on/off',
            'animationsEnabled' => 'Enable UI animations',
            'topbarEnabled' => 'Show top bar in meeting',
            'notificationAlertsEnabled' => 'Show notification alerts',
            'debug' => 'Enable debug mode for detailed logging'
        ];
    }
    
    /**
     * Get meeting URL for sharing
     */
    public static function getMeetingShareUrl($meetingId, $baseUrl = '') {
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                      '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        }
        
        return $baseUrl . '/videosdk-join.php?meetingId=' . urlencode($meetingId);
    }
    
    /**
     * Get meeting join URL with all parameters
     */
    public static function getMeetingJoinUrl($meetingId, $config = [], $baseUrl = '') {
        if (empty($baseUrl)) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                      '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        }
        
        $url = $baseUrl . '/videosdk-join.php?meetingId=' . urlencode($meetingId);
        
        // Add other parameters
        foreach ($config as $key => $value) {
            if ($value !== null && $value !== '' && $key !== 'meetingId') {
                $url .= '&' . $key . '=' . urlencode($value);
            }
        }
        
        return $url;
    }
}
?>
