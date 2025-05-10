<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'matkhau' => Hash::make('Test1234567'),
            'email' => 'test@example.com',
            'hoten' => 'Test User',
        ]);
    }
}
