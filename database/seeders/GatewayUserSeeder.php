<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GatewayUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@library.test'],
            [
                'name' => 'Library Admin',
                'password' => Hash::make('admin'),
                'role' => 'ADMIN',
            ]
        );

        User::updateOrCreate(
            ['email' => 'librarian@library.test'],
            [
                'name' => 'Library Librarian',
                'password' => Hash::make('librarian'),
                'role' => 'LIBRARIAN',
            ]
        );
    }
}
