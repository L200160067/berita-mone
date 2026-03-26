<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mone.mutudev.com'],
            [
                'name' => 'Admin M-One',
                'email' => 'admin@mone.mutudev.com',
                'password' => Hash::make('Admin@Mone2026!'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Admin user seeded: admin@mone.mutudev.com / Admin@Mone2026!');
    }
}
