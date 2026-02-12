<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'publicId'     => $this->publicId,
            'name'         => $this->name,
            'description'  => $this->description,
            'price'        => $this->price,
            'stock'        => $this->stock,
        ];
    }
}
