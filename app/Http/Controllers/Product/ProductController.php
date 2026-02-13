<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Shared\BaseController;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    /**
     * Display a listing of the resource.
     */
    public function getAll()
    {
        return $this->service->getAll();
    }

    /**
     * Display the specified resource.
     */
    public function getById($id)
    {
        return $this->service->getById($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        return $this->service->update($request->validated(), $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        return $this->service->delete($id);
    }
}
