<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseInventoryItemResource;
use App\Http\Requests\InventoryRequest;
use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Cache;

class InventoryController extends Controller
{
    public function index(InventoryRequest $request)
    {
        $filters = $request->validatedFilters();
        $perPage = $filters['per_page'] ?? Stock::DEFAULT_PER_PAGE;

        $stocks = Stock::with(['warehouse', 'item'])
            ->filter($filters)
            ->paginate($perPage);

        return InventoryResource::collection($stocks);
    }

    public function warehouseInventory($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $stocks = Cache::remember("warehouse_inventory_$id", 60, fn () => Stock::with('item')->where('warehouse_id', $id)->get());

        return response()->json([
            'warehouse' => new WarehouseResource($warehouse),
            'inventory' => WarehouseInventoryItemResource::collection($stocks),
        ]);
    }
}
