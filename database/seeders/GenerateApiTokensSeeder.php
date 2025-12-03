<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GenerateApiTokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates API tokens for users that don't have one.
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
