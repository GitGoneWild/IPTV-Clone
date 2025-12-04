# Blade Admin Panel Migration Guide

This document provides a comprehensive guide for completing the migration from Filament to Blade for the admin panel.

## Overview

The migration from Filament to Blade aims to:
- **Increase maintainability**: Pure Blade/Laravel is easier to maintain than Filament abstraction
- **Follow DRY principles**: Reusable components throughout the application
- **Consistency**: Match the existing landing page and user dashboard design
- **Flexibility**: Full control over UI/UX without framework constraints

## Current Status

### ✅ Completed (Phase 1-2)
- Core admin infrastructure (controllers, routes, layouts)
- User Management (complete CRUD implementation)
- Dashboard with statistics and recent users

### 🔄 In Progress
- Additional resource migrations (streams, categories, etc.)

### ⏳ Pending
- Settings pages migration
- Widget components
- Dependency cleanup

---

## Architecture

### Directory Structure
```
app/Http/Controllers/Admin/
├── AdminController.php       # Base controller with auth and helpers
├── DashboardController.php   # Main dashboard
└── UserController.php        # User management (reference implementation)

resources/views/admin/
├── layouts/
│   └── admin.blade.php       # Main admin layout
├── components/               # Reusable components (to be created)
├── dashboard.blade.php       # Dashboard view
└── users/
    ├── index.blade.php       # User list
    ├── create.blade.php      # Create user form
    └── edit.blade.php        # Edit user form
```

### Routes
All admin routes use the prefix `/blade-admin` and require authentication + admin role:
```php
Route::middleware(['auth', 'role:admin'])->prefix('blade-admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    // Add more resources here
});
```

---

## Migration Pattern (Step-by-Step)

Follow this pattern for each Filament resource:

### Step 1: Create Controller

Create a new controller in `app/Http/Controllers/Admin/` that extends `AdminController`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\YourModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YourModelController extends AdminController
{
    public function index(Request $request): View
    {
        $query = YourModel::query();
        
        // Add search/filter logic
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $items = $query->latest()->paginate(15)->withQueryString();
        
        return view('admin.your-model.index', compact('items'));
    }
    
    // Implement create(), store(), edit(), update(), destroy()
}
```

### Step 2: Add Routes

Add the resource route in `routes/web.php`:

```php
Route::resource('your-model', YourModelController::class);
```

### Step 3: Create Views

Create the corresponding Blade views following the User Management pattern:

#### index.blade.php
- Page header with "New" button
- Search/filter form
- Data table with actions
- Pagination

#### create.blade.php
- Back button to index
- Form sections (group related fields)
- Cancel and Submit buttons
- Error handling with `@error` directives

#### edit.blade.php
- Similar to create but with pre-filled values
- Additional metadata section if needed
- Update button instead of Create

### Step 4: Test

1. Access the new admin page
2. Test all CRUD operations
3. Verify search/filter functionality
4. Check validation and error messages
5. Confirm activity logging works

---

## Design System

### Colors
The admin panel uses the same color scheme as the landing page:

```javascript
// Homelab Purple Theme
'homelab': {
    500: '#8b5cf6',  // Primary
    600: '#7c3aed',  // Primary Dark
    700: '#6d28d9',  // Primary Darker
}

// GitHub Dark Theme
'gh': {
    'bg': '#0d1117',           // Background
    'bg-secondary': '#161b22', // Card background
    'bg-tertiary': '#21262d',  // Hover states
    'border': '#30363d',       // Borders
    'text': '#c9d1d9',         // Text
    'text-muted': '#8b949e',   // Muted text
    'accent': '#58a6ff',       // Links
    'success': '#3fb950',      // Success
    'warning': '#d29922',      // Warning
    'danger': '#f85149',       // Danger
}
```

### Typography
- Font: Inter
- Headers: font-bold, various sizes (text-3xl, text-lg, etc.)
- Body: text-sm or text-base

### Components

#### Buttons
```html
<!-- Primary Button -->
<button class="px-6 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg transition-colors">
    Action
</button>

<!-- Secondary Button -->
<button class="px-6 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
    Cancel
</button>
```

#### Cards
```html
<div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
    <!-- Content -->
</div>
```

#### Form Fields
```html
<div>
    <label for="field" class="block text-sm font-medium text-gh-text mb-2">Label *</label>
    <input type="text" 
           name="field" 
           id="field" 
           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('field') border-gh-danger @enderror">
    @error('field')
        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
    @enderror
</div>
```

#### Badges/Status
```html
<!-- Success -->
<span class="px-2 py-1 text-xs font-medium rounded bg-gh-success/10 text-gh-success">
    Active
</span>

<!-- Danger -->
<span class="px-2 py-1 text-xs font-medium rounded bg-gh-danger/10 text-gh-danger">
    Inactive
</span>
```

---

## Best Practices

### 1. DRY (Don't Repeat Yourself)
- Create reusable Blade components for common UI elements
- Extract repeated form sections into components
- Use layout inheritance (`@extends`, `@section`)

### 2. Validation
Always validate input on both client and server side:
```php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:table'],
]);
```

### 3. Activity Logging
Use Spatie Activity Log for audit trails:
```php
activity()
    ->performedOn($model)
    ->causedBy(auth()->user())
    ->withProperties(['key' => 'value'])
    ->log('Action description');
```

### 4. Flash Messages
Use session flash messages for user feedback:
```php
return redirect()->route('admin.resource.index')
    ->with('success', 'Operation completed successfully.');
```

### 5. Authorization
All admin routes already require `auth` and `role:admin` middleware. For additional checks:
```php
if ($model->id === auth()->id()) {
    return redirect()->back()->with('error', 'Cannot perform this action.');
}
```

---

## Remaining Resources to Migrate

### Priority 1 (Core Functionality)
1. **Stream Management** (`StreamResource.php`)
   - Complex with status monitoring and server relationships
   - Most used feature
   
2. **Category Management** (`CategoryResource.php`)
   - Simpler resource, good for practicing
   
3. **Bouquet Management** (`BouquetResource.php`)
   - Important for user package assignment

### Priority 2 (Content Management)
4. **Movie Management** (`MovieResource.php`)
   - TMDB integration
   
5. **Series Management** (`SeriesResource.php`)
   - Season/episode complexity
   
6. **EPG Source Management** (`EpgSourceResource.php`)

### Priority 3 (Infrastructure)
7. **Server Management** (`ServerResource.php`)
8. **Load Balancer Management** (`LoadBalancerResource.php`)
9. **Device Management** (`DeviceResource.php`)
10. **Geo Restriction Management** (`GeoRestrictionResource.php`)

### Priority 4 (Business)
11. **Invoice Management** (`InvoiceResource.php`)
    - Billing system

### Custom Pages
- **Integration Settings** (`IntegrationSettings.php`)
- **System Management** (`SystemManagement.php`)

---

## Widget Migration

Widgets should become Blade components:

```php
// Create in resources/views/components/admin/
resources/views/components/admin/
├── stats-card.blade.php
├── chart-card.blade.php
└── recent-table.blade.php
```

Example stats card component:
```blade
{{-- resources/views/components/admin/stats-card.blade.php --}}
@props(['title', 'value', 'icon', 'color' => 'homelab'])

<div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gh-text-muted">{{ $title }}</p>
            <p class="text-3xl font-bold text-gh-text mt-2">{{ $value }}</p>
        </div>
        <div class="bg-{{ $color }}-500/10 p-3 rounded-lg">
            {!! $icon !!}
        </div>
    </div>
    @isset($footer)
        <div class="mt-4 text-sm">
            {{ $footer }}
        </div>
    @endisset
</div>
```

Usage:
```blade
<x-admin.stats-card 
    title="Total Users" 
    :value="$stats['total_users']" 
    color="homelab">
    <x-slot name="icon">
        <!-- SVG icon -->
    </x-slot>
    <x-slot name="footer">
        <span class="text-gh-success">{{ $stats['active_users'] }} active</span>
    </x-slot>
</x-admin.stats-card>
```

---

## Testing Strategy

### Manual Testing Checklist
For each migrated resource:
- [ ] List page displays correctly with data
- [ ] Search/filter works as expected
- [ ] Create form validates properly
- [ ] Edit form pre-fills data correctly
- [ ] Update saves changes
- [ ] Delete removes record (with confirmation)
- [ ] Flash messages appear
- [ ] Responsive design works on mobile
- [ ] Activity log records actions

### Feature Tests (Optional)
Create tests in `tests/Feature/Admin/`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_admin_can_view_user_list()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));
            
        $response->assertOk();
    }
    
    // Add more tests...
}
```

---

## Cleanup Phase

Once all resources are migrated:

### 1. Remove Filament Dependencies
```bash
composer remove filament/filament
```

### 2. Remove Filament Files
```bash
rm -rf app/Filament
rm -rf resources/views/filament
```

### 3. Clean Up Routes
Remove Filament routes from `routes/web.php` and providers.

### 4. Update Configuration
Remove Filament service providers from `config/app.php` and `bootstrap/providers.php`.

### 5. Update Documentation
- Update README.md
- Remove Filament badges
- Add Blade admin documentation
- Update screenshots

---

## Migration Timeline

Recommended approach:
1. **Week 1-2**: Migrate Priority 1 resources (Streams, Categories, Bouquets)
2. **Week 3-4**: Migrate Priority 2 resources (Movies, Series, EPG)
3. **Week 5**: Migrate Priority 3 resources (Infrastructure)
4. **Week 6**: Migrate Priority 4 + Custom Pages
5. **Week 7**: Widget migration and component refinement
6. **Week 8**: Testing, cleanup, documentation

---

## Support & References

- **Laravel Documentation**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Alpine.js** (for interactive components): https://alpinejs.dev/
- **Heroicons** (icons): https://heroicons.com/

## Questions?

For questions or issues during migration, refer to:
1. The User Management implementation as reference
2. Existing Blade views (landing.blade.php, dashboard.blade.php)
3. Laravel best practices documentation
