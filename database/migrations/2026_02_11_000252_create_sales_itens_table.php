<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_itens', function (Blueprint $table) {
            $table->id();
            $table->uuid('publicId')->unique();

            $table->foreignId('saleId')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('productId')->constrained('products')->cascadeOnDelete();

            $table->string('productName');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_itens');
    }
};
