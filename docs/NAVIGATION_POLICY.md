# Navigation Configuration & Maintenance Guide

## Overview

This document describes the navigation configuration policy for the HomelabTV admin panel, which uses Filament PHP for its administration interface.

## Navigation Icon Policy

### The Rule

In Filament 3.x, **navigation groups and their items cannot both have icons**. This is a deliberate UX design decision by the Filament team.

Violating this rule results in the error:
```
Navigation group [X] has an icon but one or more of its items also have icons.
Either the group or its items can have icons, but not both.
```

### Our Implementation

We follow the pattern of:
- **Navigation Groups**: Label-only (no icons)
- **Individual Resources**: Have icons (for visual identification)

This approach:
- Provides better visual hierarchy
- Makes it easier to identify individual resources
- Avoids clutter in the navigation sidebar
- Follows Filament best practices

### How to Add New Navigation Items

#### Adding a New Resource

1. Create your Filament Resource using artisan:
   ```bash
   php artisan make:filament-resource YourModel
   ```

2. In your Resource class, set the navigation properties:
   ```php
   protected static ?string $navigationIcon = 'heroicon-o-your-icon';
   protected static ?string $navigationGroup = 'Your Group';
   protected static ?int $navigationSort = 1;
   ```

3. If the group doesn't exist, add it to `AdminPanelProvider.php`:
   ```php
   ->navigationGroups([
       NavigationGroup::make()
           ->label('Your Group'),
       // ... existing groups
   ])
   ```

4. **Do NOT add icons to navigation groups**. Icons go on Resources only.

#### Adding a New Navigation Group

1. Edit `app/Filament/AdminPanelProvider.php`
2. Add your group to the `navigationGroups` array:
   ```php
   NavigationGroup::make()
       ->label('New Group'),
   ```
3. Ensure you do NOT add `->icon()` to the group
4. Set `$navigationGroup` on your Resources to match the group label

### Current Navigation Structure

| Group          | Resources                                      |
|----------------|------------------------------------------------|
| Streaming      | Streams, Categories, Bouquets, EPG Sources     |
| Content        | Movies, Series                                 |
| Users & Access | Users                                          |
| System         | Servers, Load Balancers                        |

### Icon Selection

We use Heroicons (outline variant) for all navigation icons. Browse available icons at:
https://heroicons.com/

Common icons used:
- `heroicon-o-play` - Streams
- `heroicon-o-folder` - Categories
- `heroicon-o-rectangle-stack` - Bouquets
- `heroicon-o-calendar` - EPG Sources
- `heroicon-o-film` - Movies
- `heroicon-o-tv` - TV Series
- `heroicon-o-users` - Users
- `heroicon-o-server` - Servers
- `heroicon-o-globe-alt` - Load Balancers

## Troubleshooting

### Icon Conflict Error

If you see the navigation icon conflict error:

1. Check `AdminPanelProvider.php` - ensure no groups have icons
2. Check all Resources in `app/Filament/Resources/` - icons should be set here
3. Run `php artisan config:clear` to clear cached configuration
4. Run `php artisan view:clear` to clear cached views

### Navigation Not Updating

1. Clear the view cache: `php artisan view:clear`
2. Clear the config cache: `php artisan config:clear`
3. If using a production cache, run: `php artisan optimize:clear`

## Related Files

- `app/Filament/AdminPanelProvider.php` - Main panel configuration
- `app/Filament/Resources/*.php` - Individual resource configurations

---

**Last Updated:** December 2024
**Maintained by:** Development Team
