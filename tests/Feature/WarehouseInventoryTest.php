<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\Stock;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_warehouse_inventory()
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::factory()->create();
        $items = InventoryItem::factory()->count(3)->create();

        foreach ($items as $item) {
            Stock::factory()->create([
                'warehouse_id' => $warehouse->id,
                'inventory_item_id' => $item->id,
                'quantity' => rand(5, 20),
            ]);
        }

        $response = $this->getJson("/api/warehouses/{$warehouse->id}/inventory");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'warehouse' => ['id','name','location'],
                     'inventory' => [
                         '*' => [
                             'item' => ['id','name','sku','price'],
                             'quantity'
                         ]
                     ]
                 ]);

        $this->assertCount(3, $response->json('inventory'));
    }

    public function test_warehouse_not_found_returns_404()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson("/api/warehouses/999/inventory");
        $response->assertStatus(404);
    }

    public function test_empty_inventory_returns_empty_array()
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::factory()->create();

        $response = $this->getJson("/api/warehouses/{$warehouse->id}/inventory");

        $response->assertStatus(200)
                 ->assertJson([
                     'warehouse' => [
                         'id' => $warehouse->id,
                         'name' => $warehouse->name,
                         'location' => $warehouse->location,
                     ],
                     'inventory' => [],
                 ]);
    }

    public function test_unauthenticated_user_cannot_access_inventory()
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->getJson("/api/warehouses/{$warehouse->id}/inventory");

        $response->assertStatus(401);
    }
}
