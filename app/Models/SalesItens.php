<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesItens extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'saleId',
        'productId',
        'productName',
        'quantity',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId', 'id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'saleId', 'id');
    }
}

