# Nexoom - Video Conferencing Platform

A simple PHP-based video conferencing platform using Jitsi Meet API, similar to Zoom with basic features.

## Features

### Broadcaster Page (`broadcaster.php`)
- Start meetings with unique room IDs
- Microphone on/off control
- Speaker on/off control
- Screen sharing capability
- End meeting functionality
- Full Jitsi Meet integration

### Viewer Page (`viewer.php`)
- Join meetings as participants
- Microphone on/off control
- Speaker on/off control
- Raise hand functionality (with visual animation)
- Leave meeting option
- Chat integration

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
- **Video Conferencing**: Jitsi Meet External API
- **Styling**: Tailwind CSS
- **Icons**: Font Awesome

## File Structure

```
Nexoom/
├── index.php          # Homepage with role selection
├── broadcaster.php    # Broadcaster interface
├── viewer.php         # Viewer/participant interface
├── style.css          # Custom styles
└── README.md          # This file
```

## Features Implemented

✅ Two main pages (Broadcaster & Viewer)
✅ Microphone control
✅ Speaker control
✅ Screen sharing (Broadcaster)
✅ Raise hand functionality (Viewer)
✅ Modern, responsive UI
✅ Jitsi Meet integration
✅ Meeting room management

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

## Powered by

This project uses [Jitsi Meet](https://jitsi.org/) for video conferencing functionality.
