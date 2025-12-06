# Nyalife HMS - Messaging System

## Overview

The messaging system provides internal communication capabilities between system users (doctors, nurses, administrators, etc.). This system allows users to send, receive, organize, and manage messages within the hospital management system.

## Features

### Core Messaging Features
- ✅ **Send Messages**: Users can compose and send messages to other system users
- ✅ **Inbox Management**: View received messages with read/unread status
- ✅ **Sent Messages**: View messages you've sent to others
- ✅ **Message Priorities**: Support for low, normal, and high priority messages
- ✅ **Archive System**: Archive old messages for organization
- ✅ **Soft Delete**: Messages are soft-deleted (can be recovered if needed)
- ✅ **Search Functionality**: Search messages by content, subject, or sender
- ✅ **Message Statistics**: Track unread count, total messages, etc.

### User Interface Features
- ✅ **Modern Web Interface**: Clean, responsive message interface
- ✅ **Message Composition**: Rich message composition form with validation
- ✅ **Message Reading**: Full message view with participant details
- ✅ **Quick Actions**: Archive, delete, reply, forward messages
- ✅ **Real-time Status**: Automatic read status updates
- ✅ **Theme Integration**: Consistent with system's teal/magenta theme

### API Features
- ✅ **RESTful API**: Complete API for message operations
- ✅ **Authentication**: Secure API endpoints with user authentication
- ✅ **Error Handling**: Comprehensive error handling and logging
- ✅ **Data Validation**: Input validation and sanitization

## Database Schema

### Messages Table (`messages`)
```sql
CREATE TABLE messages (
    message_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    recipient_id INT(11) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
    is_read TINYINT(1) DEFAULT 0,
    is_archived TINYINT(1) DEFAULT 0,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    -- Indexes and foreign keys included
);
```

### Activity Logs Table (`activity_logs`)
```sql
CREATE TABLE activity_logs (
    activity_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## File Structure

### Backend Components

#### Models
- `includes/models/MessageModel.php` - Database operations for messages
  - Message CRUD operations
  - Search functionality
  - Statistics and counting
  - Soft delete and archival

#### Controllers

##### API Controllers
- `includes/controllers/api/CommunicationController.php` - Message API endpoints
  - `GET /api/messages/inbox` - Get inbox messages
  - `GET /api/messages/sent` - Get sent messages
  - `GET /api/messages/archived` - Get archived messages
  - `GET /api/messages/search` - Search messages
  - `GET /api/messages/users` - Get users for messaging
  - `GET /api/messages/:id` - Get specific message
  - `POST /api/messages/send` - Send new message
  - `POST /api/messages/archive` - Archive message
  - `POST /api/messages/delete` - Delete message
  - `POST /api/messages/mark-read` - Mark message as read

##### Web Controllers
- `includes/controllers/web/MessagesController.php` - Web interface controller
  - `GET /messages` - Messages inbox/list
  - `GET /messages/compose` - Compose message form
  - `GET /messages/search` - Search results
  - `GET /messages/:id` - View specific message
  - `POST /messages/send` - Send message (form submission)
  - `POST /messages/archive` - Archive message (AJAX)
  - `POST /messages/delete` - Delete message (AJAX)
  - `POST /messages/mark-read` - Mark as read (AJAX)

### Frontend Components

#### Views
- `includes/views/messages/index.php` - Main messages list (inbox/sent/archived)
- `includes/views/messages/compose.php` - Message composition form
- `includes/views/messages/show.php` - Individual message display
- `includes/views/messages/search.php` - Search results display

#### Features of Views
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Interactive Elements**: Dropdowns, modals, AJAX actions
- **Theme Integration**: Uses system CSS variables and styling
- **Accessibility**: Proper ARIA labels and keyboard navigation
- **User Experience**: Auto-save drafts, character counting, form validation

### Database Schema
- `Schema/messages_table.sql` - Database schema for messages system

## API Documentation

### Authentication
All API endpoints require authentication. The system uses session-based authentication.

### Message Object Structure
```json
{
  "message_id": 123,
  "sender_id": 1,
  "recipient_id": 2,
  "subject": "Message Subject",
  "message": "Message content...",
  "priority": "normal",
  "is_read": 0,
  "is_archived": 0,
  "is_deleted": 0,
  "created_at": "2023-12-07 10:30:00",
  "updated_at": "2023-12-07 10:30:00",
  "sender_first_name": "John",
  "sender_last_name": "Doe",
  "sender_email": "john@example.com",
  "recipient_first_name": "Jane",
  "recipient_last_name": "Smith",
  "recipient_email": "jane@example.com"
}
```

### API Endpoints

#### Get Inbox Messages
```http
GET /api/messages/inbox?page=1&limit=20&unread_only=false
```

#### Send Message
```http
POST /api/messages/send
Content-Type: application/json

{
  "recipient_id": 2,
  "subject": "Meeting Tomorrow",
  "message": "Don't forget about our meeting tomorrow at 2 PM.",
  "priority": "high"
}
```

#### Search Messages
```http
GET /api/messages/search?q=meeting&type=inbox&limit=20
```

## Usage Examples

### Sending a Message (Web Interface)
1. Navigate to `/messages/compose`
2. Select recipient from dropdown
3. Enter subject and message
4. Choose priority level
5. Click "Send Message"

### Managing Messages (Web Interface)
1. Navigate to `/messages`
2. Switch between Inbox, Sent, and Archived using sidebar
3. Use search functionality to find specific messages
4. Use dropdown actions to archive/delete messages
5. Click on message to read full content

### API Usage (JavaScript)
```javascript
// Send a message via API
fetch('/api/messages/send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        recipient_id: 12,
        subject: 'Test Message',
        message: 'This is a test message.',
        priority: 'normal'
    })
})
.then(response => response.json())
.then(data => {
    if (data.message) {
        console.log('Message sent successfully');
    }
});
```

## Security Features

- **Authentication Required**: All endpoints require valid user authentication
- **User Access Control**: Users can only access their own messages
- **Input Validation**: All inputs are validated and sanitized
- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Protection**: Output is properly escaped in views
- **CSRF Protection**: Forms use proper CSRF protection mechanisms

## Integration Points

### Notification System Integration
- Messages can trigger notifications when sent
- Unread message counts are tracked
- Real-time updates for new messages

### User Management Integration
- Uses existing user authentication system
- Integrates with user roles and permissions
- User search functionality for message composition

### Activity Logging
- All message actions are logged for audit trails
- Tracks sent, read, archived, and deleted activities

## Testing

The system has been tested with:
- ✅ Database schema creation and migration
- ✅ Message sending and receiving
- ✅ User lookup and authentication
- ✅ API endpoint functionality
- ✅ Web interface rendering
- ✅ Search functionality
- ✅ Message statistics

## Installation Notes

1. **Database Migration**: Run the SQL schema to create required tables
2. **File Permissions**: Ensure proper read/write permissions on uploaded files
3. **Configuration**: No additional configuration required beyond standard HMS setup
4. **Dependencies**: Uses existing HMS core components (DatabaseManager, Auth, etc.)

## Future Enhancements

Potential future improvements could include:
- Real-time messaging with WebSocket support
- File attachments to messages
- Message threading/conversations
- Bulk message operations
- Message templates
- Auto-reply/out-of-office messages
- Message encryption for sensitive communications
- Mobile app integration
- Email notifications for new messages

## Troubleshooting

### Common Issues

1. **Messages not showing**: Check user authentication and database connections
2. **Send functionality not working**: Verify recipient user IDs and form data
3. **Search not returning results**: Check search index and query parameters
4. **Style issues**: Ensure CSS files are loaded and variables are defined

### Debug Mode

Enable debug mode in the system configuration to get detailed error messages and SQL query logs.

---

The messaging system is now fully integrated into the Nyalife HMS and ready for production use.
