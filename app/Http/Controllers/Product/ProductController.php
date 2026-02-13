<?php

namespace App\Http\Controllers\Product;

use App\Services\Product\ProductService;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function getAll()
    {
        return $this->service->getAll();
    }

    public function getById($id)
    {
        return $this->service->getById($id);
    }

    public function store(StoreProductRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        return $this->service->update($request->validated(), $id);
    }

    public function delete($id)
    {
        return $this->service->delete($id);
    }
}

