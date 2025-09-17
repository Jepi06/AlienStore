<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_subcategories')->insert([
          ['name' => 'Pakaian Pria', 'category_id' => 1],
           ['name' => 'Pakaian Wanita', 'category_id' => 1],
            ['name' => 'Aksesoris', 'category_id' => 1],
            ['name' => 'Sepatu', 'category_id' => 1],

            // Subkategori untuk F&B (asumsi category_id = 2)
            ['name' => 'Makanan Ringan', 'category_id' => 2],
            ['name' => 'Minuman', 'category_id' => 2],
            ['name' => 'Bumbu Dapur', 'category_id' => 2],
            ['name' => 'Makanan Beku', 'category_id' => 2],

            // Subkategori untuk Sembako (asumsi category_id = 3)
            ['name' => 'Beras', 'category_id' => 3],
            ['name' => 'Gula', 'category_id' => 3],
            ['name' => 'Minyak Goreng', 'category_id' => 3],
            ['name' => 'Tepung', 'category_id' => 3],
        ]);
    }
}
