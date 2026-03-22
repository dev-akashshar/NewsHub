<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'      => 'Administrator',
                'username'  => 'admin',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        // Default Test User
        User::updateOrCreate(
            ['username' => 'user1'],
            [
                'name'      => 'Test User',
                'username'  => 'user1',
                'password'  => Hash::make('user123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );

        // Second Test User
        User::updateOrCreate(
            ['username' => 'user2'],
            [
                'name'      => 'Another User',
                'username'  => 'user2',
                'password'  => Hash::make('user123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Default users created:');
        $this->command->table(
            ['Name', 'Username', 'Password', 'Role'],
            [
                ['Administrator', 'admin',  'admin123', 'admin'],
                ['Test User',     'user1',  'user123',  'user'],
                ['Another User',  'user2',  'user123',  'user'],
            ]
        );
    }
}
