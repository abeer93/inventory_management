<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockTransferController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/warehouses/{id}/inventory', [InventoryController::class, 'warehouseInventory']);
    Route::post('/stock-transfers', [StockTransferController::class, 'store']);
});

