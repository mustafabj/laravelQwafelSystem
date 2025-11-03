<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@qwafel.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    
        User::create([
            'name' => 'Employee',
            'email' => 'employee@qwafel.com',
            'password' => Hash::make('employee123'),
            'role' => 'employee',
        ]);
    }
}
