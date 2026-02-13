<?php

namespace App\Jobs;

use App\Repositories\Sale\PixRepository;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckPixPayment implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected string $txid;
    protected PixRepository $pixRepository;

    public function __construct(string $txid, PixRepository $pixRepository)
    {
        $this->txid = $txid;
        $this->pixRepository = $pixRepository;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $paymentData = $this->pixRepository->checkCobranca($this->txid);

            if ($paymentData && isset($paymentData['status']) && $paymentData['status'] == 'paid') {
                // Busca a venda pela TXID
                $sale = Sale::where('pix_txid', $this->txid)->first();

                if ($sale) {
                    $sale->status = 'completed';
                    $sale->paid_at = Carbon::now();
                    $sale->save();
                    
                    Log::info("Venda com TXID {$this->txid} foi paga.");
                }
            } else {
                Log::info("Pagamento não realizado para a TXID {$this->txid}. Tentando novamente.");
            }
        } catch (\Exception $e) {
            Log::error("Erro ao verificar o pagamento do PIX para a TXID {$this->txid}: " . $e->getMessage());
        }
    }
}
