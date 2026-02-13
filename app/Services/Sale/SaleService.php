<?php

namespace App\Services\Sale;

use App\Models\Sale;
use App\Models\SalesItens;
use App\Models\Product;
use App\Repositories\Sale\PixRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleService
{
    protected PixRepository $pixRepository;

    public function __construct(PixRepository $pixRepository)
    {
        $this->pixRepository = $pixRepository;
    }

    /**
     * Cria uma venda, decrementa estoque e gera cobrança PIX
     *
     * @param array $items
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSale(array $items)
    {
        DB::beginTransaction();

        try {
            $total = 0;

            // Cria a venda pendente
            $sale = Sale::create([
                'publicId' => uniqid(),
                'total' => 0,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para {$product->name}");
                }

                // Decrementa estoque
                $product->decrement('stock', $item['quantity']);

                $total += $product->price * $item['quantity'];

                // Cria item da venda
                SalesItens::create([
                    'publicId' => uniqid(),
                    'saleId' => $sale->id,
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            // Atualiza total da venda
            $sale->total = $total;
            $sale->save();

            // Gera cobrança PIX
            $pixData = $this->pixRepository->createCobranca([
                'company' => [
                    'id' => 1, // Ajustar para a empresa real
                    'document' => '17089484000190', // CNPJ da empresa
                    'values' => [], // Caso precise passar dados extras
                ],
                'value' => $total,
                'currentDate' => Carbon::now(),
                'renewalDate' => Carbon::now(),
                'numberOfMonths' => 1,
                'valueCost' => 0,
            ]);

            // Salva TXID e QRCode na venda
            $sale->update([
                'pix_txid' => $pixData['txid'],
                'qrcode' => $pixData['qrcode'],
            ]);

            DB::commit();

            return response()->json([
                'sale' => $sale,
                'qrcode' => $pixData['qrcode'],
                'txid' => $pixData['txid'],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Processa webhook PIX da SGBr e atualiza status da venda
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPixWebhook(array $data)
    {
        $sale = Sale::where('pix_txid', $data['txid'])->first();

        if (!$sale) {
            return response()->json(['message' => 'Venda não encontrada'], 404);
        }

        if ($sale->status === 'completed') {
            return response()->json(['message' => 'Venda já paga'], 200);
        }

        // Atualiza status da venda
        $sale->update([
            'status' => 'completed',
            'paid_at' => isset($data['pix'][0]['pagoEm'])
                ? Carbon::parse($data['pix'][0]['pagoEm'])
                : Carbon::now(),
        ]);

        return response()->json(['message' => 'Venda atualizada com sucesso'], 200);
    }
}
