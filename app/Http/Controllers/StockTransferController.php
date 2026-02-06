<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Services\StockTransferService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function store(StockTransferRequest $request, StockTransferService $service) 
    {
        $transfer = $service->transfer($request->validated());

        return (new StockTransferResource($transfer))
        ->response()
        ->setStatusCode(201);
    }
}
