# Messaging and Notes System - User Guide

## Overview
A complete messaging and notes system has been added to the B2B Admin Panel, allowing team communication through groups and sharing important notes.

## Features

### 1. Messaging Groups Management
**URL:** `/b2b-panel/messaging/groups`

**Capabilities:**
- Create messaging groups with custom names
- Assign multiple users to each group
- Edit existing groups (change name or members)
- Delete groups (removes all messages)
- View member count per group

**How to Use:**
1. Navigate to Messaging in the sidebar
2. Click "Manage Groups" button
3. Click "New Group" to create a group
4. Enter group name and select members
5. Click "Save Group"

### 2. Messaging Interface
**URL:** `/b2b-panel/messaging`

**Capabilities:**
- Real-time group messaging (auto-refreshes every 5 seconds)
- View all your assigned groups
- Send messages to group members
- See message history
- Visual distinction between your messages and others'
- Timestamp on all messages
- Unread message counter badge in sidebar

**How to Use:**
1. Navigate to Messaging in the sidebar
2. Select a group from the left panel
3. Type your message in the text area
4. Click "Send" or press Enter
5. Messages appear instantly and refresh automatically

**Message Features:**
- Your messages appear on the right in blue
- Others' messages appear on the left in white
- Each message shows sender name and timestamp
- Auto-scroll to latest message
- Unread badge clears when you open the messaging page

### 3. Notes System
**URL:** `/b2b-panel/notes`

**Capabilities (Admin Only):**
- Create notes with title and content
- Set visibility:
  - **General**: Visible to all users
  - **Group-specific**: Only visible to specific messaging group members
- Edit existing notes
- Delete notes
- View author and creation date

**How to Use:**
1. Navigate to Notes in the sidebar
2. Click "New Note" button (admin only)
3. Enter title and content
4. Select visibility:
   - Choose "General (Everyone)" for all users
   - Choose a specific group to limit visibility
5. Click "Save Note"

**Note Display:**
- General notes have yellow/amber background
- Group-specific notes have blue background
- Shows author, visibility, and creation date
- Grid layout adapts to screen size

### 4. Dashboard Integration

**Notes Widget:**
- Displays 3 most recent notes relevant to the user
- Quick link to view all notes
- Visible at the bottom of the dashboard
- Only shows notes the user has permission to see

## Permissions

### Admin Users
- Can create/edit/delete messaging groups
- Can see all messaging groups
- Can create/edit/delete notes
- Can set note visibility

### Regular Users
- Can only see messaging groups they're assigned to
- Can send/receive messages in their groups
- Can view notes visible to them (general or their groups)
- Cannot create notes

## Technical Details

### Data Storage
- Messaging groups: `b2b_messaging_groups` option
- Messages per group: `b2b_messages_{group_id}` option
- Notes: `b2b_notes` option
- Unread count: `b2b_unread_messages` user meta

### Message Limits
- Up to 500 messages stored per group (oldest removed automatically)
- Messages refresh every 5 seconds
- No file attachments (text only)

### Security
- All AJAX requests check user authentication
- Users can only access their assigned groups
- Only admins can manage groups and notes
- All inputs are sanitized

## URLs Reference

| Page | URL | Access |
|------|-----|--------|
| Messaging Groups | `/b2b-panel/messaging/groups` | Admin only |
| Messaging | `/b2b-panel/messaging` | All users (their groups) |
| Notes | `/b2b-panel/notes` | All users (based on visibility) |

## Icon Legend

- 💬 **Messaging**: Real-time group communication
- 📝 **Notes**: Important announcements and information
- 👥 **Groups**: User group management
- 🔔 **Badge**: Unread message indicator

## Tips

1. **Create groups by department or team** (e.g., "Sales Team", "Warehouse Staff")
2. **Use general notes for company-wide announcements**
3. **Use group notes for team-specific information**
4. **Check the unread badge** to stay updated on new messages
5. **Keep message history clean** - older messages auto-delete after 500

## Future Enhancements (Not Yet Implemented)

- File attachments in messages
- @mentions for specific users
- Message search functionality
- Email notifications for new messages
- Message read receipts
- Typing indicators
- Message editing/deletion
