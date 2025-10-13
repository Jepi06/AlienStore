<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates a default admin user for first-time setup.
     * Credentials:
     * - Email: admin@alienstore.com
     * - Password: admin123
     * - Role: Admin (role_id: 1)
     */
    public function run(): void
    {
        // Check if admin user already exists to avoid duplicates
        $existingAdmin = DB::table('sec_user')
            ->where('email', 'admin@alienstore.com')
            ->first();

        if (!$existingAdmin) {
            // Get Admin role ID (should be 1 from SecRoleSeeder)
            $adminRole = DB::table('sec_role')
                ->where('name', 'Admin')
                ->first();

            if ($adminRole) {
                DB::table('sec_user')->insert([
                    'name' => 'Administrator',
                    'email' => 'admin@alienstore.com',
                    'password' => Hash::make('admin123'),
                    'role_id' => $adminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "✅ Default admin user created successfully!\n";
                echo "   Email: admin@alienstore.com\n";
                echo "   Password: admin123\n";
                echo "   ⚠️  Please change the password after first login!\n\n";
            } else {
                echo "❌ Error: Admin role not found. Please run SecRoleSeeder first.\n";
            }
        } else {
            echo "ℹ️  Admin user already exists. Skipping creation.\n";
        }
    }
}