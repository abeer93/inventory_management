<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\Stock;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_inventory_with_pagination()
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::factory()->create();
        $items = InventoryItem::factory()->count(20)->create();

        foreach ($items as $item) {
            Stock::factory()->create([
                'warehouse_id' => $warehouse->id,
                'inventory_item_id' => $item->id,
                'quantity' => rand(1,50),
            ]);
        }

        $response = $this->getJson("/api/inventory?per_page=10");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'warehouse' => ['id','name'],
                             'item' => ['id','name','sku','price'],
                             'quantity'
                         ]
                     ],
                     'links',
                     'meta'
                 ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_inventory_filter_by_item_name()
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::factory()->create();
        $matching = InventoryItem::factory()->create(['name' => 'SpecialLaptop']);
        $other = InventoryItem::factory()->count(3)->create();

        Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $matching->id,
            'quantity' => 10
        ]);

        $response = $this->getJson("/api/inventory?item_name=SpecialLaptop");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => 'SpecialLaptop']);
    }

    public function test_inventory_returns_empty_if_no_match()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson("/api/inventory?item_name=NonExistingItem");

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_cannot_fetch_inventory()
    {
        $response = $this->getJson("/api/inventory");

        $response->assertStatus(401);
    }
}
