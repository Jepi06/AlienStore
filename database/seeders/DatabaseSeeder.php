<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\SecRoleSeeder;
use Database\Seeders\ProSeeder;
use Database\Seeders\AdminUserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \\App\\Models\\User::factory(10)->create();

        // \\App\\Models\\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(SecRoleSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(ProductCategorySeeder::class);
        $this->call(ProSeeder::class);
    }
}
