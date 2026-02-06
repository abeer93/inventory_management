<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryItem;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryItem::insert([
            ['name' => 'Laptop', 'sku' => 'LP-001', 'price' => 1200],
            ['name' => 'Keyboard', 'sku' => 'KB-002', 'price' => 80],
            ['name' => 'Mouse', 'sku' => 'MS-003', 'price' => 40],
            ['name' => 'Monitor', 'sku' => 'MN-004', 'price' => 300],
        ]);
    }
}
