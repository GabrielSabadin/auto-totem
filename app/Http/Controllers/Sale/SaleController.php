<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Shared\BaseController;
use App\Services\Sale\SaleService;
use App\Http\Requests\Sales\StoreSaleRequest;
use Illuminate\Http\Request;

class SaleController extends BaseController
{
    public function __construct(SaleService $service)
    {
        parent::__construct($service);
    }

    /**
     * Cria uma nova venda e gera cobrança PIX.
     */
    public function store(StoreSaleRequest $request)
    {
        return $this->service->createSale($request->validated());
    }

    /**
     * Webhook da SGBr para atualizar status do pagamento.
     */
    public function pixWebhook(Request $request)
    {
        // Recebe dados do webhook e repassa para o service
        return $this->service->processPixWebhook($request->all());
    }
}
