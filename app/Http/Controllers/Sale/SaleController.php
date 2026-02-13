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

    public function show($id)
    {
        return \App\Models\Sale::findOrFail($id);
    }


    public function store(StoreSaleRequest $request)
    {
        return $this->service->createSale(
            $request->validated()['products']
        );
    }

    public function pixWebhook(Request $request)
    {
        return $this->service->processPixWebhook($request->all());
    }
}

