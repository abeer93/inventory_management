<?php

namespace Tests\Unit;

use App\Events\LowStockDetected;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\StockTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockTransferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockTransferService();
    }

    public function test_transfer_successful()
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        $fromStock = Stock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 50,
        ]);

        $toStock = Stock::factory()->create([
            'warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 20,
        ]);

        $transferData = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id'   => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity'          => 10,
        ];

        $transfer = $this->service->transfer($transferData);

        $this->assertDatabaseHas('stocks', [
            'id' => $fromStock->id,
            'quantity' => 40,
        ]);

        $this->assertDatabaseHas('stocks', [
            'id' => $toStock->id,
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $transfer->id,
            'quantity' => 10,
        ]);
    }

    public function test_transfer_throws_exception_when_insufficient_stock()
    {
        $this->expectException(ValidationException::class);

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 5,
        ]);

        $transferData = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id'   => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity'          => 10,
        ];

        $this->service->transfer($transferData);
    }

    public function test_low_stock_event_is_triggered()
    {
        Event::fake();

        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 15,
        ]);

        Stock::factory()->create([
            'warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 0,
        ]);

        $transferData = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id'   => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity'          => 10,
        ];

        $this->service->transfer($transferData);

        Event::assertDispatched(LowStockDetected::class);
    }

    public function test_to_stock_is_created_if_not_exists()
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $item = InventoryItem::factory()->create();

        Stock::factory()->create([
            'warehouse_id' => $fromWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 20,
        ]);

        $transferData = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id'   => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity'          => 10,
        ];

        $this->service->transfer($transferData);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $toWarehouse->id,
            'inventory_item_id' => $item->id,
            'quantity' => 10,
        ]);
    }
}
