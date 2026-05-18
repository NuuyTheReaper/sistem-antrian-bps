<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat atau update user admin default
        User::updateOrCreate(
            ['email' => 'admin@bpstegal.com'],
            [
                'name'     => 'Admin BPS',
                'password' => Hash::make('bpskotategal123'),
                'role'     => 'admin',
            ]
        );
    }
}
