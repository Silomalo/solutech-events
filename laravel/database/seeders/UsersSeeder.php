<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Admin Member',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin@admin.com'),
                'user_system_category' => 1,
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@staff.com',
                'password' => Hash::make('staff@staff.com'),
                'user_system_category' => 2,
            ],
            [
                'name' => 'User Member',
                'email' => 'user@user.com',
                'password' => Hash::make('user@user.com'),
                'user_system_category' => 3,
            ],
        ];

        foreach ($data as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']], // search condition
                [
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => $admin['password'],
                    'user_system_category' => $admin['user_system_category'],
                ]
            );
        }
    }
}