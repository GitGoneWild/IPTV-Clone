<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user for testing
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@homelabtv.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_admin' => true,
            'is_reseller' => false,
            'max_connections' => 5,
            'allowed_outputs' => ['m3u', 'xtream', 'enigma2'],
        ]);

        $admin->assignRole('admin');
        $admin->generateApiToken();

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@homelabtv.com');
        $this->command->info('Password: password');

        // Create a test regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@homelabtv.com',
            'username' => 'testuser',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_admin' => false,
            'is_reseller' => false,
            'max_connections' => 1,
            'allowed_outputs' => ['m3u', 'xtream'],
        ]);

        $user->assignRole('user');
        $user->generateApiToken();

        $this->command->info('Test user created successfully!');
        $this->command->info('Email: user@homelabtv.com');
        $this->command->info('Password: password');

        // Create a test guest user
        $guest = User::create([
            'name' => 'Guest User',
            'email' => 'guest@homelabtv.com',
            'username' => 'guestuser',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_admin' => false,
            'is_reseller' => false,
            'max_connections' => 1,
            'allowed_outputs' => ['m3u'],
        ]);

        $guest->assignRole('guest');
        $guest->generateApiToken();

        $this->command->info('Guest user created successfully!');
        $this->command->info('Email: guest@homelabtv.com');
        $this->command->info('Password: password');
    }
}
