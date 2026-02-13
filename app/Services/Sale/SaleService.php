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

            $sale = Sale::create([
                'total' => 0,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para {$product->name}");
                }

                $product->decrement('stock', $item['quantity']);

                $total += $product->price * $item['quantity'];

                SalesItens::create([
                    'saleId' => $sale->id,
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $sale->total = $total;
            $sale->save();

            $pixData = $this->pixRepository->createCobranca([
                'company' => [
                    'id' => 1, 
                    'document' => '17089484000190',
                    'values' => [],
                ],
                'value' => $total,
                'currentDate' => Carbon::now(),
                'renewalDate' => Carbon::now(),
                'numberOfMonths' => 1,
                'valueCost' => 0,
            ]);

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
        logger()->info('Webhook PIX recebido:', ['data' => $data]);

        $txid = $data['txid'] ?? 
                $data['pix']['txid'] ?? 
                $data['txId'] ?? 
                null;

        if (!$txid) {
            return response()->json([
                'message' => 'TXID não fornecido',
                'received_data' => $data
            ], 400);
        }

        $sale = Sale::where('pix_txid', $txid)->first();

        if (!$sale) {
            logger()->warning('Venda não encontrada para TXID:', ['txid' => $txid]);
            return response()->json(['message' => 'Venda não encontrada'], 404);
        }

        if ($sale->status === 'completed') {
            logger()->info('Venda já foi pagada:', ['txid' => $txid]);
            return response()->json(['message' => 'Venda já paga'], 200);
        }

        $sale->update([
            'status' => 'completed',
            'paid_at' => isset($data['pix'][0]['pagoEm'])
                ? Carbon::parse($data['pix'][0]['pagoEm'])
                : Carbon::now(),
        ]);

        logger()->info('Venda atualizada com sucesso:', ['txid' => $txid, 'sale_id' => $sale->id]);
        return response()->json(['message' => 'Venda atualizada com sucesso'], 200);
    }
}
