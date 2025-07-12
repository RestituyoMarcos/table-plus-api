<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'),
        ]);

        // Crear usuario regular
        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('user1234'),
        ]);
    }
}
