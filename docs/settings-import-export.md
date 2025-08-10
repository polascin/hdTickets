# Settings Import/Export Feature Documentation

## Overview

The Settings Import/Export feature allows users to backup, restore, and transfer their HD Tickets preferences across accounts or devices. This comprehensive system includes export functionality with multiple formats, import validation with conflict resolution, and automatic backup creation.

## Features

### 🔄 Export Functionality
- **JSON Export**: Complete settings export in structured JSON format (recommended)
- **CSV Export**: Tabular format export for spreadsheet applications
- **Selective Export**: Choose specific categories to export
- **Security**: Automatically excludes sensitive data (passwords, API keys, 2FA secrets)
- **Metadata**: Includes export timestamp, version, and application info

### 📥 Import System
- **File Validation**: Comprehensive validation of imported data structure
- **Import Preview**: Preview all changes before applying them
- **Merge Strategies**: 
  - **Merge**: Update existing settings, keep unchanged ones
  - **Overwrite**: Replace all existing settings with imported ones
  - **Skip Existing**: Only add new settings, don't change existing ones
- **Conflict Resolution**: Gracefully handle conflicts with existing data

### 🎯 Exportable Categories

#### General Preferences
- Theme settings (light, dark, auto)
- Display preferences (density, sidebar state)
- Dashboard configuration (widgets order, refresh intervals)
- Alert preferences (thresholds, timing)
- Performance settings (lazy loading, compression)

#### Favorite Teams
- Sports team preferences with priority levels
- League and sport type associations
- Notification settings per team
- Team aliases and metadata

#### Favorite Venues
- Event venue preferences
- Location and capacity information
- Venue type classifications
- Geographic coordinates and search preferences

#### Price Preferences
- Price alert thresholds and ranges
- Automatic purchase settings
- Seat preferences and categories
- Alert frequency configurations

#### Notification Settings
- Channel preferences (email, push, SMS)
- Alert timing and quiet hours
- Escalation settings
- Frequency preferences

## Technical Implementation

### Backend Architecture

#### Controller: `SettingsExportController`
- **Routes**: `/settings-export/*`
- **Methods**:
  - `index()`: Display the import/export interface
  - `exportSettings()`: Generate and download settings export
  - `previewImport()`: Validate and preview import changes
  - `importSettings()`: Process the actual import
  - `resolveConflicts()`: Handle conflict resolution
  - `resetToDefaults()`: Reset settings with backup option

#### Models Used
- `UserPreference`: General user preferences storage
- `UserFavoriteTeam`: Sports team preferences
- `UserFavoriteVenue`: Venue preferences
- `UserPricePreference`: Price alert preferences
- `UserNotificationSettings`: Notification channel settings

#### Security Features
- **Sensitive Data Exclusion**: Automatically filters out passwords, API keys, 2FA secrets
- **File Validation**: Strict JSON structure validation
- **Size Limits**: 2MB maximum file size
- **CSRF Protection**: All forms protected with CSRF tokens
- **User Isolation**: All operations scoped to authenticated user

### Frontend Architecture

#### JavaScript Module: `SettingsExportManager`
- **Auto-initialization**: Automatically loads when relevant elements are present
- **Event Handling**: Comprehensive form interaction management
- **File Processing**: Drag-and-drop and file selection handling
- **AJAX Communication**: All backend communication via fetch API
- **UI Feedback**: Loading states, progress bars, status messages

#### User Interface Components
- **Category Selector**: Grid-based category selection with icons
- **Format Selector**: Radio button format selection
- **Import Dropzone**: Drag-and-drop file upload area
- **Merge Strategy**: Radio button merge strategy selection
- **Preview System**: Detailed change preview with conflict highlighting
- **Progress Tracking**: Visual progress indicators
- **Status Messages**: Success, error, and warning message display

## Data Structure

### Export Format

```json
{
  "meta": {
    "version": "1.0",
    "exported_at": "2024-01-15T12:00:00.000000Z",
    "exported_by": 123,
    "application": "HD Tickets",
    "categories": ["preferences", "teams", "venues", "prices", "notifications"]
  },
  "data": {
    "preferences": {
      "notifications": {
        "email_notifications": {
          "value": true,
          "data_type": "boolean"
        },
        "theme": {
          "value": "dark",
          "data_type": "string"
        }
      }
    },
    "teams": [
      {
        "sport_type": "football",
        "team_name": "Manchester United",
        "team_city": "Manchester",
        "league": "Premier League",
        "priority": 5,
        "email_alerts": true,
        "push_alerts": true,
        "sms_alerts": false
      }
    ],
    "venues": [
      {
        "venue_name": "Old Trafford",
        "city": "Manchester",
        "state_province": "Greater Manchester",
        "country": "GBR",
        "venue_types": ["stadium"],
        "priority": 5,
        "email_alerts": true,
        "push_alerts": true,
        "sms_alerts": false
      }
    ],
    "prices": [
      {
        "preference_name": "Premier League Matches",
        "sport_type": "football",
        "event_category": "regular_season",
        "min_price": 50,
        "max_price": 200,
        "preferred_quantity": 2,
        "seat_preferences": ["lower_level"],
        "price_drop_threshold": 15.00,
        "email_alerts": true,
        "push_alerts": true,
        "sms_alerts": false,
        "alert_frequency": "immediate"
      }
    ],
    "notifications": {
      "email": true,
      "push": true,
      "sms": false,
      "slack": false,
      "discord": false,
      "telegram": false
    }
  }
}
```

## Usage Guide

### Exporting Settings

1. Navigate to **Profile → Settings Import/Export**
2. Select categories to export:
   - General Preferences
   - Favorite Teams
   - Favorite Venues
   - Price Preferences
   - Notification Settings
3. Choose export format (JSON recommended)
4. Click **Export Selected Settings**
5. File will be automatically downloaded

### Importing Settings

1. Navigate to **Profile → Settings Import/Export**
2. Drag and drop JSON file or click to select
3. Choose import strategy:
   - **Merge**: Update existing, keep unchanged
   - **Overwrite**: Replace all with imported
   - **Skip Existing**: Only add new items
4. Click **Preview Import** to review changes
5. Review the preview showing:
   - Total changes count
   - New items to be added
   - Existing items to be updated
   - Conflicts (if any)
6. Click **Confirm Import** to apply changes

### Resetting Settings

1. Navigate to **Profile → Settings Import/Export**
2. Scroll to **Reset Settings** section
3. Optionally enable **Create backup before reset**
4. Select categories to reset
5. Click **Reset Selected Settings**
6. Confirm the action in the dialog

## API Endpoints

### Export Settings
```
POST /settings-export/export
Content-Type: multipart/form-data

Parameters:
- categories[]: Array of category names
- format: 'json' or 'csv'

Response: File download
```

### Preview Import
```
POST /settings-export/preview
Content-Type: multipart/form-data

Parameters:
- import_file: JSON file
- merge_strategy: 'merge', 'overwrite', or 'skip_existing'

Response:
{
  "success": true,
  "preview": {
    "total_changes": 15,
    "changes": {...},
    "conflicts": [...],
    "new_items": {...}
  },
  "validation": {
    "valid": true,
    "errors": []
  }
}
```

### Import Settings
```
POST /settings-export/import
Content-Type: multipart/form-data

Parameters:
- import_file: JSON file
- merge_strategy: 'merge', 'overwrite', or 'skip_existing'
- preview_confirmed: true
- categories[]: Optional array of categories to import

Response:
{
  "success": true,
  "message": "Settings imported successfully",
  "result": {
    "imported_count": 15,
    "conflicts": [],
    "errors": []
  }
}
```

### Reset Settings
```
POST /settings-export/reset

Parameters:
- categories[]: Array of categories to reset
- create_backup: boolean
- confirm_reset: true

Response:
{
  "success": true,
  "message": "Settings reset to defaults successfully",
  "backup_file": "user-backups/123/backup-123-2024-01-15-12-00-00.json",
  "reset_result": {
    "reset_count": 25
  }
}
```

## Error Handling

### Validation Errors
- **File Format**: Only JSON files accepted
- **File Size**: Maximum 2MB limit
- **Structure**: Strict validation of data structure
- **Required Fields**: Validation of required fields per category

### Import Conflicts
- **Duplicate Detection**: Identifies existing items
- **Conflict Resolution**: User choice for handling conflicts
- **Preview System**: Shows all changes before applying
- **Rollback**: Transaction-based import with rollback on failure

### Security Measures
- **Data Sanitization**: All input data sanitized
- **User Isolation**: All operations scoped to authenticated user
- **Sensitive Data**: Automatic exclusion of passwords, tokens, etc.
- **File Validation**: Comprehensive JSON structure validation

## Performance Considerations

### Optimization Features
- **Caching**: User preferences cached for performance
- **Cache Invalidation**: Automatic cache clearing after imports
- **Transaction Safety**: Database transactions for data integrity
- **Progress Tracking**: Visual feedback for long operations

### Scalability
- **File Size Limits**: 2MB maximum prevents abuse
- **Background Processing**: Large imports can be queued
- **Resource Management**: Memory-efficient file processing
- **Rate Limiting**: API endpoints are rate-limited

## Troubleshooting

### Common Issues

#### Import Fails
- **Solution**: Check file format (must be valid JSON)
- **Solution**: Verify file size (must be under 2MB)
- **Solution**: Ensure data structure matches expected format

#### Missing Categories
- **Solution**: Check if categories exist in source export
- **Solution**: Verify user has permission to import category
- **Solution**: Check for data corruption in JSON file

#### Conflicts Not Resolved
- **Solution**: Review merge strategy selection
- **Solution**: Use preview to identify conflict sources
- **Solution**: Export current settings before import for backup

### Support Information
- **Logs**: All operations logged for debugging
- **Error Messages**: Detailed error messages provided
- **User Feedback**: Status messages show operation progress
- **Documentation**: Comprehensive help text in interface

## Future Enhancements

### Planned Features
- **Scheduled Exports**: Automatic backup generation
- **Cloud Storage**: Integration with cloud storage providers
- **Team Sharing**: Share settings between team members
- **Version Control**: Track changes to settings over time
- **Advanced Filtering**: More granular export/import control

### Integration Possibilities
- **API Integration**: Third-party tool integration
- **Mobile Apps**: Mobile app settings sync
- **Browser Extension**: Browser extension preferences sync
- **Webhook Support**: Automated import/export triggers
