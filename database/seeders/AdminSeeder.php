<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fotomajstor.com'],
            [
                'name' => 'Administrator',
                'password' => 'Q!CL7d*Zr3p.UxgQ!CL',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
