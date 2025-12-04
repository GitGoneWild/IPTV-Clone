<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard access
            'view dashboard',
            
            // Stream permissions
            'view streams',
            'view playlist',
            'view epg',
            
            // Admin permissions
            'manage users',
            'manage streams',
            'manage categories',
            'manage servers',
            'manage bouquets',
            'manage epg',
            'view system status',
            'manage settings',
            'view activity logs',
            
            // Reseller permissions
            'manage clients',
            'view reseller dashboard',
            'assign packages',
            
            // Billing permissions
            'view invoices',
            'manage invoices',
            'create invoices',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Guest role - can only see a greeting page
        $guest = Role::create(['name' => 'guest']);
        $guest->givePermissionTo([
            'view dashboard', // Can access dashboard but will see restricted content
        ]);

        // User role - can access streams after package assignment
        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'view dashboard',
            'view streams',
            'view playlist',
            'view epg',
            'view invoices',
        ]);

        // Reseller role - can manage clients and assign packages
        $reseller = Role::create(['name' => 'reseller']);
        $reseller->givePermissionTo([
            'view dashboard',
            'view streams',
            'view playlist',
            'view epg',
            'manage clients',
            'view reseller dashboard',
            'assign packages',
            'view invoices',
            'create invoices',
        ]);

        // Admin role - full access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
