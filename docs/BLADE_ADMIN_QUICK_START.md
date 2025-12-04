# Blade Admin Panel - Quick Start Guide

## Overview

The Blade Admin Panel is a custom-built admin interface that provides full control over the IPTV system. It replaces the Filament framework with pure Laravel Blade views for better maintainability and flexibility.

## Access

- **URL**: `https://your-domain.com/admin`
- **Requirements**: Admin role authentication

## Features

### Dashboard (`/admin`)
- System statistics overview
  - Total users (active/guest breakdown)
  - Total streams (online percentage)
  - Total categories
  - Total bouquets
- Recent users table
- Visual stat cards with color-coded metrics

### User Management (`/admin/users`)

#### List View
- Paginated user list (15 per page)
- Search by name, email, or username
- Filter by:
  - Role (guest, user, reseller, admin)
  - Status (active/inactive)
- Sort by creation date (newest first)
- Quick actions: Edit, Delete

#### Create User
- User Information:
  - Name, email, username
  - Password with confirmation
- Role & Permissions:
  - Primary role assignment
  - Parent reseller (optional)
  - Active status toggle
- Subscription & Limits:
  - Expiry date
  - Max concurrent connections
  - Reseller credits
  - Allowed output formats (M3U, Xtream, Enigma2)

#### Edit User
- All creation fields
- Optional password update
- User metadata display:
  - API token
  - Created date
  - Last login date
  - Assigned packages count
- Self-deletion prevention
- Activity logging for all changes

#### Delete User
- Confirmation required
- Self-deletion prevention
- Activity log entry
- Success/error flash messages

## Design System

### Colors
The admin panel uses a consistent purple/GitHub dark theme:

| Color | Purpose | Hex |
|-------|---------|-----|
| Homelab Purple | Primary actions, links | `#8b5cf6` |
| GitHub Dark | Background | `#0d1117` |
| GitHub Secondary | Cards, sections | `#161b22` |
| GitHub Tertiary | Hover states | `#21262d` |
| Success | Active, online | `#3fb950` |
| Warning | Warnings | `#d29922` |
| Danger | Errors, inactive | `#f85149` |

### Typography
- **Font**: Inter
- **Headers**: Bold, various sizes
- **Body**: 14px (text-sm)
- **Muted**: Gray for secondary text

### Components

#### Role Badges
Color-coded role indicators:
- 🔴 Admin (red)
- 🟡 Reseller (yellow)
- 🟢 User (green)
- ⚪ Guest (gray)

#### Status Indicators
- ✅ Active (green)
- ❌ Inactive (red)

#### Buttons
- **Primary**: Purple background, white text
- **Secondary**: Gray background, white text
- **Danger**: Red text for delete actions

## Usage Examples

### Creating a New User

1. Navigate to `/admin/users`
2. Click "New User" button
3. Fill in required fields:
   - Name: `John Doe`
   - Email: `john@example.com`
   - Username: `johndoe`
   - Password: `SecurePass123!`
   - Confirm Password: `SecurePass123!`
4. Select role: `user`
5. Set subscription limits:
   - Max Connections: `2`
   - Expiry Date: `2024-12-31`
6. Select output formats: `M3U`, `Xtream`
7. Click "Create User"

### Searching for Users

1. Navigate to `/admin/users`
2. Use the search box:
   - Type name, email, or username
3. Apply filters:
   - Role: Select from dropdown
   - Status: Active/Inactive
4. Click "Filter"
5. Click "Reset" to clear filters

### Editing a User

1. Navigate to `/admin/users`
2. Find the user in the list
3. Click "Edit" in the actions column
4. Update fields as needed
5. Leave password blank to keep current password
6. Click "Update User"

### Activity Logging

All admin actions are logged using Spatie Activity Log:
- User creation
- User updates
- User deletion
- Role changes

View logs in the activity log table (when implemented).

## Security

### Authentication
- All routes require authentication (`auth` middleware)
- All routes require admin role (`role:admin` middleware)
- Session-based authentication

### Authorization
- Self-deletion prevention (admins cannot delete themselves)
- Role-based access control via Spatie Permission
- CSRF protection on all forms

### Validation
- Server-side validation on all inputs
- Email uniqueness checks
- Password strength requirements (min 8 characters)
- Username format validation (alphanumeric and dashes)

### Activity Logging
- All CRUD operations logged
- Causality tracking (who performed the action)
- Property tracking (what changed)
- Timestamp recording

## Navigation

### Header Navigation
- **Dashboard**: System overview
- **Users**: User management
- **Streams**: (To be implemented)
- **Content**: (To be implemented)
- **Settings**: (To be implemented)
- **View Site**: Return to user portal

### User Dropdown
- Display current user name
- Logout option

## Flash Messages

The admin panel uses flash messages for user feedback:

### Success Messages (Green)
- "User created successfully."
- "User updated successfully."
- "User deleted successfully."

### Error Messages (Red)
- "You cannot delete your own account."
- Validation errors
- Database errors

## Responsive Design

The admin panel is fully responsive:
- **Desktop**: Full layout with sidebar
- **Tablet**: Compact layout
- **Mobile**: Stacked layout with hamburger menu

## API Integration

### Activity Log API
Uses Spatie Activity Log for audit trails:
```php
activity()
    ->performedOn($user)
    ->causedBy(auth()->user())
    ->withProperties(['role' => 'admin'])
    ->log('User created via admin panel');
```

### Flash Messages API
Uses Laravel session flash:
```php
return redirect()->route('admin.users.index')
    ->with('success', 'Operation completed successfully.');
```

## Troubleshooting

### Common Issues

**Issue**: Cannot access admin panel
- **Solution**: Ensure you have admin role assigned

**Issue**: 404 error on `/admin`
- **Solution**: Run `php artisan route:clear` and `php artisan cache:clear`

**Issue**: Flash messages not appearing
- **Solution**: Check session configuration in `.env`

**Issue**: User creation fails with duplicate error
- **Solution**: Username or email already exists, try different values

**Issue**: Cannot delete user
- **Solution**: Check if you're trying to delete yourself (prevented)

## Development

### Adding New Resources

Follow the pattern in `docs/BLADE_ADMIN_MIGRATION.md`:

1. Create controller in `app/Http/Controllers/Admin/`
2. Add routes in `routes/web.php`
3. Create views in `resources/views/admin/`
4. Test thoroughly

### Code Style

- Follow Laravel conventions
- Use type hints
- Add PHPDoc comments
- Keep methods small and focused
- Use dependency injection

### Testing

Manual testing checklist:
- ✅ List page displays data
- ✅ Search works
- ✅ Filters work
- ✅ Create form validates
- ✅ Edit form pre-fills
- ✅ Update saves changes
- ✅ Delete removes record
- ✅ Flash messages appear
- ✅ Activity logs record

## Support

For issues or questions:
1. Check `docs/BLADE_ADMIN_MIGRATION.md` for detailed guidance
2. Review reference implementation in User Management
3. Check Laravel documentation for framework features
4. Review Tailwind CSS documentation for styling

## Future Plans

See `docs/MIGRATION_IMPLEMENTATION_SUMMARY.md` for:
- Remaining resources to migrate
- Timeline and priorities
- Dependency cleanup plan
- Testing strategy

## Quick Reference

| Feature | Route | Method | View |
|---------|-------|--------|------|
| Dashboard | `/admin` | GET | `admin.dashboard` |
| List Users | `/admin/users` | GET | `admin.users.index` |
| Create User | `/admin/users/create` | GET | `admin.users.create` |
| Store User | `/admin/users` | POST | - |
| Edit User | `/admin/users/{id}/edit` | GET | `admin.users.edit` |
| Update User | `/admin/users/{id}` | PUT/PATCH | - |
| Delete User | `/admin/users/{id}` | DELETE | - |

## Credits

Built with:
- Laravel 12
- Tailwind CSS
- Alpine.js
- Heroicons
- Spatie Laravel Permission
- Spatie Laravel Activity Log
