# VideoSDK Integration for Nexoom

This document describes the VideoSDK integration implemented in your PHP-based video conferencing platform.

## Overview

The VideoSDK integration provides professional-grade video calling capabilities using the VideoSDK.live API. This integration includes:

- **Real-time video/audio calling**
- **Screen sharing**
- **Live chat**
- **Meeting recording**
- **Live streaming**
- **Whiteboard collaboration**
- **Polling system**
- **Participant management**

## Files Created/Modified

### New Files
- `includes/videosdk.php` - Core VideoSDK integration class
- `videosdk-meeting.php` - Enhanced meeting interface with VideoSDK
- `api/videosdk.php` - REST API endpoints for VideoSDK operations
- `test-videosdk.php` - Test page for VideoSDK functionality

### Modified Files
- `meeting.php` - Updated to use VideoSDK integration
- `home.php` - Updated links to use VideoSDK-powered meetings

## API Configuration

Your VideoSDK credentials are configured in `includes/videosdk.php`:

```php
$videoSDK = new VideoSDK(
    '0fc8e1a5-c073-407c-9bf4-153442433432',  // API Key
    '208769a959cf753f2e71f1f3552b601763c6d9bf2d991bed1e9e54392159382e'  // Secret Key
);
```

## Features Implemented

### 1. Meeting Management
- **Create Meeting**: Generate new VideoSDK meetings
- **Validate Meeting**: Check if a meeting exists
- **Get Meeting Details**: Retrieve meeting information
- **End Meeting**: Terminate meetings
- **Participant Management**: Add/remove participants

### 2. Video Calling Features
- **HD Video**: High-definition video calling
- **Clear Audio**: Advanced audio processing
- **Screen Sharing**: Share desktop/screen content
- **Live Chat**: Real-time messaging
- **Recording**: Start/stop meeting recordings
- **Live Streaming**: Stream meetings to external platforms

### 3. User Interface
- **Modern Design**: Beautiful, responsive interface
- **Control Panel**: Intuitive meeting controls
- **Participant Grid**: Dynamic video layout
- **Status Indicators**: Real-time meeting status
- **Chat Panel**: Integrated messaging system

### 4. Permissions & Security
- **Role-based Access**: Different permissions for admin/broadcaster/viewer
- **JWT Authentication**: Secure token-based authentication
- **Meeting Validation**: Verify meeting access rights

## Usage

### Starting a Meeting
1. Navigate to `home.php`
2. Click "Start Meeting" to create a new meeting
3. Or enter a meeting ID to join an existing meeting

### API Endpoints

The following API endpoints are available at `/api/videosdk.php`:

#### Create Meeting
```
POST /api/videosdk.php/create
Content-Type: application/json

{
    "meetingId": "optional_custom_id",
    "customRoomId": "optional_room_id"
}
```

#### Validate Meeting
```
GET /api/videosdk.php/validate/{meetingId}
```

#### Get Meeting Details
```
GET /api/videosdk.php/get/{meetingId}
```

#### End Meeting
```
POST /api/videosdk.php/end/{meetingId}
```

#### Get Participants
```
GET /api/videosdk.php/participants/{meetingId}
```

#### Start Recording
```
POST /api/videosdk.php/start-recording/{meetingId}
Content-Type: application/json

{
    "webhookUrl": "optional_webhook_url"
}
```

#### Stop Recording
```
POST /api/videosdk.php/stop-recording/{meetingId}
```

#### Start Live Stream
```
POST /api/videosdk.php/start-livestream/{meetingId}
Content-Type: application/json

{
    "rtmpUrls": ["rtmp://example.com/live/stream1"]
}
```

#### Stop Live Stream
```
POST /api/videosdk.php/stop-livestream/{meetingId}/{streamId}
```

#### Generate Token
```
GET /api/videosdk.php/token
```

## Configuration Options

The VideoSDK integration supports extensive configuration options:

### Meeting Configuration
- **Region**: `sg001` (Singapore)
- **Max Resolution**: `hd`
- **Layout Type**: `GRID`, `SPOTLIGHT`, `SIDEBAR`
- **Layout Priority**: `SPEAKER`, `PIN`

### Feature Toggles
- **Screen Share**: Enabled
- **Chat**: Enabled
- **Recording**: Enabled
- **Live Streaming**: Enabled
- **Whiteboard**: Enabled
- **Polling**: Enabled
- **Raise Hand**: Enabled

### Permissions
- **Self Controls**: Users can toggle their own mic/camera
- **Leave Meeting**: Users can leave meetings
- **End Meeting**: Only admins/broadcasters can end meetings
- **Recording Control**: Only admins/broadcasters can control recording

## Testing

Use the test page to verify VideoSDK functionality:

1. Navigate to `test-videosdk.php`
2. Check test results for:
   - Token generation
   - Meeting creation
   - Frontend configuration
3. Test video meeting functionality

## Error Handling

The integration includes comprehensive error handling:

- **API Errors**: Proper HTTP status codes and error messages
- **Network Issues**: Timeout handling and retry logic
- **Permission Errors**: Role-based access control
- **Validation Errors**: Input validation and sanitization

## Security Considerations

- **JWT Tokens**: Secure authentication with expiration
- **API Key Protection**: Credentials stored securely
- **Input Validation**: All inputs are validated and sanitized
- **HTTPS Required**: All API calls use HTTPS
- **CORS Headers**: Proper cross-origin resource sharing

## Browser Compatibility

The VideoSDK integration works with modern browsers that support:
- WebRTC
- MediaDevices API
- ES6+ JavaScript features

Supported browsers:
- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## Troubleshooting

### Common Issues

1. **Meeting Not Starting**
   - Check browser permissions for camera/microphone
   - Verify API credentials are correct
   - Check network connectivity

2. **Video/Audio Issues**
   - Ensure browser has camera/microphone access
   - Check device permissions
   - Try refreshing the page

3. **API Errors**
   - Verify API key and secret are correct
   - Check API endpoint URLs
   - Review error logs for details

### Debug Mode

Enable debug mode by setting `debug: true` in the VideoSDK configuration. This will provide detailed console logs for troubleshooting.

## Support

For VideoSDK-specific issues, refer to:
- [VideoSDK Documentation](https://docs.videosdk.live/)
- [VideoSDK GitHub](https://github.com/videosdk-live/videosdk-rtc-react-prebuilt-ui)

For Nexoom platform issues, check the application logs and error messages.

## License

This integration uses the VideoSDK.live service. Please review their terms of service and pricing for production use.
