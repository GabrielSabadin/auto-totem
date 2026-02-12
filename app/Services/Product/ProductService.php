<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProductService
{
    public function getAll()
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }

    public function getById($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }

    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $product = Product::create($data);

            DB::commit();
            return new ProductResource($product);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao criar produto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);
            $product->update($data);

            DB::commit();
            return new ProductResource($product);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao atualizar produto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);
            $product->delete();

            DB::commit();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao excluir produto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
