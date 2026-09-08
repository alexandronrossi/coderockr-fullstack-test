<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('SEED_ADMIN_PASSWORD', 'password'),
        ]);

        User::factory()->owner()->create([
            'name' => 'Owner',
            'email' => env('SEED_OWNER_EMAIL', 'owner@example.com'),
            'password' => env('SEED_OWNER_PASSWORD', 'password'),
        ]);
    }
}
