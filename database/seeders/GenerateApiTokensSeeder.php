<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GenerateApiTokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates API tokens for users that don't have one.
     *
     * Note: This seeder only generates tokens for users with null api_token.
     * To regenerate a token (e.g., for a compromised token):
     * 1. Set the user's api_token to null in the database, or
     * 2. Call $user->generateApiToken() directly in code/tinker, or
     * 3. Add an admin feature to regenerate tokens through the UI.
     */
    public function run(): void
    {
        $users = User::whereNull('api_token')->get();

        $this->command->info("Generating API tokens for {$users->count()} users...");

        foreach ($users as $user) {
            $user->generateApiToken();
            $this->command->line("Generated token for user: {$user->username}");
        }

        $this->command->info('API tokens generated successfully!');
    }
}
