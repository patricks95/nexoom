# Nexoom - Video Conferencing Platform

A production-ready PHP-based video conferencing platform using WebRTC technology, similar to Zoom with professional features.

## Features

### Broadcaster Features
- Start meetings with unique room IDs
- Microphone on/off control
- Video on/off control
- Screen sharing capability
- Real-time chat system
- Professional meeting controls

### Viewer Features
- Join meetings as participants
- Microphone on/off control
- Video on/off control
- Raise hand functionality (with visual animation)
- Real-time chat system
- Leave meeting option

## Installation

1. Place all files in your web server directory (e.g., `htdocs` for XAMPP)
2. Ensure PHP is running on your server
3. Open `index.php` in your browser

## Usage

1. **Starting a Meeting (Broadcaster)**:
   - Click "Start Meeting" on the homepage
   - This will create a unique meeting room
   - Share the meeting URL with participants

2. **Joining a Meeting (Viewer)**:
   - Click "Join Meeting" on the homepage
   - Enter the meeting ID or use the shared URL
   - Use the raise hand feature to get attention

## Technical Details

- **Backend**: PHP with session management
- **Frontend**: HTML5, CSS3, JavaScript
- **Video Conferencing**: WebRTC (no external dependencies)
- **Styling**: Tailwind CSS
- **Icons**: Font Awesome

## File Structure

```
Nexoom/
├── index.php          # Homepage with role selection
├── video-chat.php     # Main video conferencing interface
├── style.css          # Custom styles
└── README.md          # This file
```

## Features Implemented

✅ **Production-ready video conferencing**
✅ **Microphone on/off control**
✅ **Video on/off control**
✅ **Screen sharing (Broadcaster)**
✅ **Raise hand functionality (Viewer)**
✅ **Real-time chat system**
✅ **Modern, responsive UI**
✅ **WebRTC integration (no external dependencies)**
✅ **Meeting room management**
✅ **Error handling and recovery**
✅ **Device conflict resolution**

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Modern web browser with WebRTC support
- Internet connection for Jitsi Meet API

## Production Ready

This project is production-ready and uses pure WebRTC technology for video conferencing functionality without any external dependencies.
