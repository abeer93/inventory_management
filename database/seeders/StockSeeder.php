<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\InventoryItem;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $items = InventoryItem::all();

        foreach ($warehouses as $warehouse) {
            foreach ($items as $item) {
                Stock::create([
                    'warehouse_id' => $warehouse->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => rand(20, 100),
                ]);
            }
        }
    }
}
