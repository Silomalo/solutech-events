<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Developer',
                'email' => 'silomalojoseph@gmail.com',
                'password' => Hash::make('silomalojoseph@gmail.com'),
            ],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin@admin.com'),
            ],
        ];

        foreach ($data as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']], // search condition
                [
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => $admin['password'],
                ]
            );
        }
    }
}