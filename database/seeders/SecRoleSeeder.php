<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SecRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sec_role')->insert([
            [
                'name' => 'Admin',
                'description' => 'Administrator - full access',
                'active' => 1
            ],
            [
                'name' => 'User',
                'description' => 'Regular user - limited access',
                'active' => 1
            ],
            [
                'name' => 'Viewer',
                'description' => 'Can only view data',
                'active' => 1
            ],
        ]);
    }
}
