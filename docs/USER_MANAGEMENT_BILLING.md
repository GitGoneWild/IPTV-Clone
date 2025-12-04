# User Management & Billing Integration

## Overview

HomelabTV now features a comprehensive user management system with role-based access control (RBAC) using Spatie Laravel-Permission and integrated billing functionality.

## Table of Contents

- [User Roles](#user-roles)
- [User Registration & Signup](#user-registration--signup)
- [Role Escalation](#role-escalation)
- [Billing Integration](#billing-integration)
- [Admin Operations](#admin-operations)
- [API Permissions](#api-permissions)

---

## User Roles

The system implements four distinct user roles with graduated access levels:

### 1. Guest Role
**Purpose**: Default role for newly registered users awaiting package assignment.

**Access Level**:
- ✅ View dashboard (restricted greeting page)
- ❌ No stream access
- ❌ No playlist or EPG access
- ❌ No content viewing

**Automatic Escalation**: Guest users are automatically upgraded to "User" role when a package (bouquet) is assigned.

### 2. User Role
**Purpose**: Standard users with stream access after package assignment.

**Access Level**:
- ✅ View dashboard with streams
- ✅ Access assigned streams
- ✅ View and download playlists (M3U, Xtream API)
- ✅ Access EPG data
- ✅ View invoices
- ❌ Cannot manage other users
- ❌ Cannot access admin panel

### 3. Reseller Role
**Purpose**: Partners who can manage their own clients and create invoices.

**Access Level**:
- ✅ All User permissions
- ✅ Manage client accounts
- ✅ Create and manage invoices
- ✅ Assign packages to clients
- ✅ View reseller dashboard
- ❌ Cannot access system settings
- ❌ Cannot manage other resellers

### 4. Admin Role
**Purpose**: Full system access for administrators.

**Access Level**:
- ✅ Full access to admin panel
- ✅ Manage all users, resellers, and guests
- ✅ Manage streams, categories, servers
- ✅ Manage bouquets (packages)
- ✅ System configuration
- ✅ View activity logs
- ✅ Billing management

---

## User Registration & Signup

### Registration Process

1. **Access Registration Page**
   - URL: `/register`
   - Available to unauthenticated users only

2. **Required Information**
   - Full Name
   - Email Address (unique)
   - Username (unique, alphanumeric with dashes/underscores)
   - Password (confirmation required)

3. **Automatic Setup**
   - User account created with `is_active = true`
   - Assigned "Guest" role automatically
   - API token generated for Xtream API compatibility
   - Activity logged for audit trail

4. **Post-Registration**
   - User is automatically logged in
   - Redirected to dashboard showing welcome page
   - Welcome page explains guest status and next steps

### Guest Welcome Page

New users see a dedicated welcome page explaining:
- Current guest status
- Package assignment requirement
- What happens when package is assigned
- How to contact administrator

**Location**: `resources/views/pages/guest-welcome.blade.php`

---

## Role Escalation

### Guest → User Upgrade

**Trigger**: Automatic when first package (bouquet) is assigned to a guest user.

**Process**:
1. Admin assigns bouquet to guest user in admin panel
2. System detects guest has packages
3. `User::upgradeFromGuestToUser()` method is called
4. Guest role is removed
5. User role is assigned
6. Activity is logged

**Implementation Locations**:
- `app/Models/User.php` - `upgradeFromGuestToUser()` method
- `app/Filament/Resources/UserResource/Pages/EditUser.php` - `afterSave()` hook
- `app/Services/BillingService.php` - `processPaymentAndAssignPackages()` method

### Manual Role Assignment

Admins can manually change roles in the admin panel:
1. Navigate to Users & Access → Users
2. Edit user
3. Select role from dropdown: Guest, User, Reseller, or Admin
4. Legacy `is_admin` and `is_reseller` fields sync automatically

---

## Billing Integration

### Package-Based Billing

Packages in HomelabTV are represented by **Bouquets** - collections of streams, movies, and series.

### Invoice Creation

**Manual Invoice Creation** (Admin Panel):
1. Navigate to Billing → Invoices
2. Click "New Invoice"
3. Fill in details:
   - Select user
   - Enter amount and currency
   - Select packages (bouquets) to assign
   - Set due date
   - Add description
4. Invoice is created with status: `pending`

**Programmatic Invoice Creation**:
```php
use App\Services\BillingService;

$billingService = app(BillingService::class);

$invoice = $billingService->createPackageInvoice(
    user: $user,
    bouquetIds: [1, 2, 3],
    amount: 29.99,
    currency: 'USD',
    reseller: $reseller, // optional
    options: [
        'due_date' => now()->addDays(7),
        'description' => 'Monthly subscription',
    ]
);
```

### Payment Processing

**Method 1: Admin Panel** (Manual Payment):
1. Navigate to invoice in admin panel
2. Click "Mark as Paid" action
3. Select payment method (cash, credit card, bank transfer, etc.)
4. Enter payment reference (optional)
5. Confirm

**Method 2: Programmatic**:
```php
$billingService->processPaymentAndAssignPackages(
    invoice: $invoice,
    paymentMethod: 'credit_card',
    paymentReference: 'TXN123456'
);
```

**Automatic Actions on Payment**:
- Invoice status changed to `paid`
- `paid_at` timestamp recorded
- All bouquets in invoice line items assigned to user
- Guest users automatically upgraded to User role
- Activity logged for audit

### Free Package Assignment

For complimentary or promotional packages:

```php
$invoice = $billingService->assignFreePackage(
    user: $user,
    bouquetIds: [1, 2],
    reseller: $reseller // optional
);
```

This creates an invoice with $0.00 amount and immediately processes it.

### Invoice Statuses

- **pending**: Awaiting payment
- **paid**: Payment received, packages assigned
- **cancelled**: Invoice cancelled (no assignment)
- **refunded**: Payment refunded (packages remain assigned)

### Billing Dashboard

Users can view their billing information:
- Total invoices
- Pending/paid/overdue invoices
- Total amount spent
- Pending payment amount
- Assigned packages count

```php
$summary = $billingService->getUserBillingSummary($user);
```

---

## Admin Operations

### Creating Users

**Via Admin Panel**:
1. Navigate to Users & Access → Users
2. Click "New User"
3. Fill in user details
4. Select role
5. Optionally assign bouquets
6. Save

**Important**: New users without packages will remain as "Guest" until packages are assigned.

### Assigning Packages

**Method 1: Direct Assignment** (Free):
1. Edit user in admin panel
2. Select bouquets in "Bouquets" section
3. Save
4. System automatically upgrades guest to user

**Method 2: Via Billing** (Tracked):
1. Create invoice with selected bouquets
2. Mark invoice as paid
3. Packages assigned automatically
4. Billing history maintained

**Recommendation**: Use billing method for accountability and tracking.

### Managing Resellers

1. Create user or edit existing user
2. Set role to "Reseller"
3. Optionally set credit limit
4. Reseller can now:
   - View their clients
   - Create invoices for clients
   - Assign packages to clients

### Monitoring Activity

All user and role-related actions are logged using Spatie Activity Log:
- User registration
- Role changes
- Package assignments
- Invoice payments
- Login attempts

View logs in: System → Activity Log

---

## API Permissions

### REST API (Laravel Sanctum)

API access requires authentication token. Permissions checked via middleware:

```php
// Example API route with permission check
Route::middleware(['auth:sanctum', 'can:view streams'])->get('/api/streams', ...);
```

### Xtream Codes API

Xtream API checks user status and package assignment:
- Guest users: API authentication fails
- User/Reseller/Admin: Full API access based on assigned bouquets

**Authentication**: Uses API token (recommended) or password (legacy compatibility)

---

## Database Schema

### Roles & Permissions Tables
Created by Spatie Permission package:
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

### Key Fields on Users Table
- `is_admin`: Boolean (synced with admin role)
- `is_reseller`: Boolean (synced with reseller role)
- `is_active`: Boolean (account active status)
- `expires_at`: DateTime (subscription expiry)

### Invoices Table
- `invoice_number`: Unique identifier
- `user_id`: Foreign key to users
- `reseller_id`: Optional foreign key (who created invoice)
- `amount`: Decimal amount
- `status`: pending|paid|cancelled|refunded
- `line_items`: JSON array of bouquets/packages
- `due_date`, `paid_at`: Timestamps

---

## Code Examples

### Check User Role
```php
// Spatie Permission method (recommended)
if ($user->hasRole('admin')) { ... }

// Multiple roles
if ($user->hasAnyRole(['admin', 'reseller'])) { ... }

// Check permission
if ($user->can('manage users')) { ... }
```

### Assign Package with Billing
```php
use App\Services\BillingService;

$billingService = app(BillingService::class);

// Create invoice
$invoice = $billingService->createPackageInvoice(
    user: $user,
    bouquetIds: [1, 2, 3],
    amount: 29.99,
    currency: 'USD'
);

// Process payment
$billingService->processPaymentAndAssignPackages(
    invoice: $invoice,
    paymentMethod: 'credit_card'
);
```

### Manual Role Upgrade
```php
// If you need to manually upgrade a user
if ($user->hasRole('guest') && $user->hasPackageAssigned()) {
    $user->upgradeFromGuestToUser();
}
```

---

## Security Considerations

1. **Role Assignment**: Only admins can assign roles
2. **Package Assignment**: Tracked via billing for audit trail
3. **API Tokens**: Generated securely, unique per user
4. **Activity Logging**: All sensitive operations logged
5. **Middleware Protection**: Routes protected by role and permission checks

---

## Future Enhancements

Potential features for future releases:
- Automated billing cycles
- Subscription renewals
- Payment gateway integration (Stripe, PayPal)
- Email notifications for invoices
- Reseller commission tracking
- Package expiration management

---

## Support

For questions or issues:
1. Check the Troubleshooting guide: `docs/TROUBLESHOOTING.md`
2. Review activity logs in admin panel
3. Check Laravel logs: `storage/logs/laravel.log`

---

*Documentation Version: 1.0*  
*Last Updated: December 2025*
