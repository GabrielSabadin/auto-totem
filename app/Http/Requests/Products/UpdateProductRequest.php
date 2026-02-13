<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['sometimes', 'string', 'min:3', 'max:255'],
            'price'       => ['sometimes', 'decimal:0,2'],
            'stock'       => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
