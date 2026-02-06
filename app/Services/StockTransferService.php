<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\Stock;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function __construct()
    {
    }
 
    public function transfer(array $data)
    {
        return DB::transaction(function () use ($data) {

            $fromStock = Stock::where([
                'warehouse_id' => $data['from_warehouse_id'],
                'inventory_item_id' => $data['inventory_item_id'],
            ])->lockForUpdate()->firstOrFail();

            if ($fromStock->quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock'
                ]);
            }

            $toStock = Stock::firstOrCreate(
                [
                    'warehouse_id' => $data['to_warehouse_id'],
                    'inventory_item_id' => $data['inventory_item_id'],
                ],
                ['quantity' => 0]
            );

            $fromStock->decrement('quantity', $data['quantity']);
            $toStock->increment('quantity', $data['quantity']);

            $transfer = StockTransfer::create($data);

            if ($fromStock->quantity < 10) {
                event(new LowStockDetected($fromStock));
            }

            return $transfer;
        });
    }
}
