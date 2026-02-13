<?php

namespace App\Http\Controllers\Sale;

use App\Services\Sale\SaleService;
use App\Http\Requests\Sales\StoreSaleRequest;
use Illuminate\Http\Request;

class SaleController
{
    protected $service;

    public function __construct(SaleService $service)
    {
        $this->service = $service;
    }

    public function store(StoreSaleRequest $request)
    {
        return $this->service->createSale($request->validated());
    }

    public function pixWebhook(Request $request)
    {
        return $this->service->processPixWebhook($request->all());
    }
}

