<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\StockTransfer;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Event;
use App\Events\LowStockDetected;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_transfer_stock()
    {
        Sanctum::actingAs(User::factory()->create());

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 50,
        ]);

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['quantity' => 10]);

        $this->assertDatabaseHas('stock_transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 40,
        ]);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);
    }

    public function test_cannot_transfer_more_than_available_stock()
    {
        Sanctum::actingAs(User::factory()->create());

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
        ]);

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantity']);
    }

    public function test_low_stock_event_is_dispatched()
    {
        Event::fake();

        Sanctum::actingAs(User::factory()->create());

        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $from->id,
            'inventory_item_id' => $item->id,
            'quantity' => 15,
        ]);

        $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        Event::assertDispatched(LowStockDetected::class);
    }

    public function test_unauthenticated_user_cannot_transfer()
    {
        $response = $this->postJson('/api/stock-transfers', []);
        $response->assertStatus(401);
    }

    public function test_validation_fails_when_required_fields_missing()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/stock-transfers', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'from_warehouse_id',
                    'to_warehouse_id',
                    'inventory_item_id',
                    'quantity'
                ]);
    }

    public function test_validation_fails_for_invalid_field_types()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => 'abc',
            'to_warehouse_id'   => 'xyz',
            'inventory_item_id' => 'one',
            'quantity'          => -5,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'from_warehouse_id',
                    'to_warehouse_id',
                    'inventory_item_id',
                    'quantity',
                ]);
    }

    public function test_validation_fails_if_warehouses_or_item_do_not_exist()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => 999,
            'to_warehouse_id'   => 998,
            'inventory_item_id' => 997,
            'quantity'          => 10,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'from_warehouse_id',
                    'to_warehouse_id',
                    'inventory_item_id',
                ]);
    }
    
    public function test_validation_fails_if_from_warehouse_equals_to_warehouse()
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();
        Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $warehouse->id,
            'to_warehouse_id'   => $warehouse->id,
            'inventory_item_id' => $item->id,
            'quantity'          => 5,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['from_warehouse_id']);
    }
}
