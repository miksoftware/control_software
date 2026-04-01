<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@miksoftware.com'],
            [
                'name'              => 'Administrador MikSoftware',
                'email'             => 'admin@miksoftware.com',
                'password'          => Hash::make('MikSoft2026!'),
                'email_verified_at' => now(),
            ]
        );
    }
}
