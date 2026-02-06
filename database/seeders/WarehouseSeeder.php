<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::insert([
            ['name' => 'Main Warehouse', 'location' => 'Cairo'],
            ['name' => 'Secondary Warehouse', 'location' => 'Alexandria'],
            ['name' => 'Backup Warehouse', 'location' => 'Giza'],
        ]);
    }
}
