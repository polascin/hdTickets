# Profile Picture Upload System

## Overview

The Profile Picture Upload System is a comprehensive solution for handling user profile image uploads in the HD Tickets application. It provides secure file upload, validation, image processing, cropping functionality, and optimization.

## Features

### ✅ Completed Features

- **Secure File Upload**: Protected file upload with CSRF validation
- **File Validation**: Size limits (5MB max), format restrictions (JPG, JPEG, PNG, WEBP)
- **Image Processing**: Automatic resizing and optimization using Intervention Image
- **Multiple Sizes**: Generates thumbnail (150x150), medium (300x300), and large (500x500) versions
- **Image Cropping**: Client-side cropping functionality with Cropper.js
- **WebP Conversion**: Automatic conversion to WebP format for optimal compression
- **Drag & Drop**: Modern drag-and-drop interface for file uploads
- **Activity Logging**: All profile picture changes are logged
- **Old File Cleanup**: Automatically removes old profile pictures when new ones are uploaded
- **Real-time Preview**: Live preview of profile picture changes
- **Responsive Design**: Mobile-friendly upload interface

## Architecture

### Backend Components

#### ProfilePictureController
Location: `app/Http/Controllers/ProfilePictureController.php`

**Key Methods:**
- `upload()`: Handles file upload with optional cropping
- `crop()`: Applies cropping to existing images
- `delete()`: Removes profile pictures
- `info()`: Returns profile picture information
- `getUploadLimits()`: Returns upload constraints

#### ProfilePictureUploadRequest
Location: `app/Http/Requests/ProfilePictureUploadRequest.php`

**Validation Rules:**
- File must be an image (jpg, jpeg, png, webp)
- Maximum size: 5MB
- Minimum dimensions: 100x100px
- Maximum dimensions: 4000x4000px

#### User Model Enhancements
Location: `app/Models/User.php`

**New Methods:**
- `getProfileDisplay()`: Enhanced profile display with URL handling
- `getProfilePictureSizes()`: Returns all available picture sizes
- `getProfilePictureUrl($size)`: Gets picture URL by size

### Frontend Components

#### JavaScript Manager
Location: `public/js/profile-picture-manager.js`

**Key Features:**
- File validation and preview
- Drag and drop support
- Cropping integration with Cropper.js
- Real-time UI updates
- Error handling and notifications

#### CSS Styles
Location: `public/css/profile-picture.css`

**Features:**
- Modern, responsive design
- Loading states and animations
- Drag and drop visual feedback
- Modal styling for crop interface
- Dark mode support

#### Blade Component
Location: `resources/views/components/profile-picture-upload.blade.php`

**Features:**
- Reusable component with prop support
- Integrated with CSS timestamp system
- CDN resources for Cropper.js
- Accessibility considerations

## File Storage

### Directory Structure
```
storage/app/public/profile-pictures/
├── profile_{user_id}_{timestamp}_{random}_thumbnail.webp
├── profile_{user_id}_{timestamp}_{random}_medium.webp
└── profile_{user_id}_{timestamp}_{random}_large.webp
```

### Naming Convention
- Format: `profile_{user_id}_{timestamp}_{random}_{size}.webp`
- Example: `profile_123_20240110120000_abc12345_medium.webp`

### Image Optimization
- **Format**: WebP for optimal compression
- **Quality**: 90% for medium/large, 80% for thumbnails
- **Sharpening**: Applied to thumbnails for clarity
- **Sizes**:
  - Thumbnail: 150x150px (navigation, lists)
  - Medium: 300x300px (profile pages, default)
  - Large: 500x500px (full-size viewing)

## API Endpoints

### Upload Profile Picture
```http
POST /profile/picture/upload
Content-Type: multipart/form-data

Parameters:
- profile_picture: File (required)
- crop_data: JSON string (optional)
```

### Crop Existing Picture
```http
POST /profile/picture/crop
Content-Type: application/json

Parameters:
- crop_data: JSON object with x, y, width, height
```

### Delete Profile Picture
```http
DELETE /profile/picture/delete
```

### Get Picture Information
```http
GET /profile/picture/info
```

### Get Upload Limits
```http
GET /profile/picture/limits
```

## Usage Examples

### Basic Upload Component
```blade
<x-profile-picture-upload :user="$user" />
```

### JavaScript Integration
```javascript
// Listen for profile picture updates
document.addEventListener('profilePictureUpdated', function(event) {
    console.log('Profile picture updated:', event.detail);
});

// Manually trigger upload
if (window.profilePictureManager) {
    window.profilePictureManager.triggerFileSelect();
}
```

### Getting Profile Pictures in PHP
```php
// Get default profile display
$profile = $user->getProfileDisplay();

// Get specific size
$mediumPicture = $user->getProfilePictureUrl('medium');

// Get all available sizes
$sizes = $user->getProfilePictureSizes();
```

## Security Considerations

### File Validation
- MIME type checking
- File extension validation
- Magic number verification
- Size limitations

### Upload Protection
- CSRF token validation
- Authentication required
- Rate limiting (via route middleware)
- User-specific file access

### File Storage Security
- Files stored outside web root
- Unique filename generation
- Automatic old file cleanup
- No executable file uploads

## Configuration

### Upload Limits
- Maximum file size: 5MB (configurable)
- Allowed formats: JPG, JPEG, PNG, WEBP
- Minimum dimensions: 100x100px
- Maximum dimensions: 4000x4000px

### Image Processing
- Output format: WebP
- Quality settings: 80-90%
- Generated sizes: 150px, 300px, 500px
- Aspect ratio: 1:1 (square)

## Error Handling

### Backend Errors
- Validation failures return 422 with detailed messages
- File system errors are logged and return generic error messages
- Missing files return 404 responses

### Frontend Errors
- File validation errors shown immediately
- Upload progress and loading states
- Graceful fallbacks for missing features
- User-friendly error messages

## Integration with Existing Systems

### Activity Logging
All profile picture operations are automatically logged using the existing ActivityLogger service:
- Upload events with file details
- Crop operations with coordinates
- Delete operations

### CSS Timestamp System
The system integrates with the existing CSS timestamp system to prevent caching issues:
```blade
<link href="{{ asset('css/profile-picture.css') }}?v={{ app('css.timestamp') }}" rel="stylesheet">
```

### User Model Integration
The system extends the existing User model without breaking changes:
- Backward compatible profile display
- Enhanced getProfileDisplay() method
- Additional convenience methods

## Testing

### Manual Testing Checklist
- [ ] File upload with valid images
- [ ] File validation (size, format, dimensions)
- [ ] Cropping functionality
- [ ] Multiple size generation
- [ ] Delete functionality
- [ ] Drag and drop interface
- [ ] Mobile responsiveness
- [ ] Error handling
- [ ] Activity logging

### Browser Compatibility
- Modern browsers with File API support
- Graceful degradation for older browsers
- Mobile browser support
- Accessibility features

## Deployment Notes

### Server Requirements
- PHP GD or ImageMagick extension
- Write permissions to storage directory
- Adequate disk space for image storage

### Laravel Requirements
- Intervention Image package
- Laravel Storage facade
- Activity logging service
- CSS timestamp service

### Apache Configuration
Ensure proper mime type handling and file size limits in Apache configuration on Ubuntu 24.04 LTS.

## Maintenance

### File Cleanup
Old profile pictures are automatically deleted when new ones are uploaded. For additional cleanup:
```bash
# Find orphaned profile pictures
find storage/app/public/profile-pictures -name "profile_*" -mtime +30
```

### Performance Monitoring
- Monitor disk usage in profile-pictures directory
- Track image processing performance
- Monitor upload success/failure rates

## Future Enhancements

### Potential Improvements
- [ ] Image filters and effects
- [ ] Batch processing for multiple images
- [ ] Integration with cloud storage (AWS S3)
- [ ] Advanced cropping tools
- [ ] Image optimization webhooks
- [ ] Bulk user profile picture operations

### API Enhancements
- [ ] RESTful API for external integrations
- [ ] Webhook notifications for profile changes
- [ ] Batch operations endpoint
- [ ] Image transformation API

## Troubleshooting

### Common Issues
1. **Upload fails**: Check file permissions and disk space
2. **Images not displaying**: Verify storage symlink exists
3. **Cropping not working**: Ensure Cropper.js is loaded
4. **Large file uploads**: Check PHP upload_max_filesize
5. **WebP not supported**: Verify GD/ImageMagick has WebP support

### Debug Commands
```bash
# Check storage permissions
ls -la storage/app/public/profile-pictures/

# Verify storage symlink
ls -la public/storage

# Check PHP configuration
php -i | grep -E "upload_max_filesize|post_max_size"
```
