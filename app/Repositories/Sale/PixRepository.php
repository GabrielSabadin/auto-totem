<?php

namespace App\Repositories\Sale;

use DateTimeZone;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PixRepository
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = 'https://pix.sgbr.com.br/v3/api/cobrancas';
        $this->token  = env('TOKEN_WSRF');
    }

    /**
     * Cria uma cobrança PIX na SGBr
     *
     * @param array $data
     * @return array
     */
    public function createCobranca(array $data): array
    {
        $dataPix = [
            'empresa' => [
                'cnpjCpf' => $data['company']['document'] ?? '17089484000190',
            ],
            'calendario' => [
                'expiracao' => 8400,
            ],
            'valor' => [
                'original' => (float) number_format($data['value'], 2, '.', ''),
            ],
            'origem' => 'Toten',
            'repasse' => [
                'exigir' => false,
                'desconto' => 0
            ],
            'nota' => [
                'exigir' => false,
            ],
            'webhooks' => [
                [
                    'url' => env('APP_URL', 'http://localhost') . route('sales.pix-webhook', [], false),
                    'descricao' => 'Webhook Toten'
                ]
            ],
        ];

        $client = new Client();

        try {
            $response = $client->post($this->baseUrl, [
                'headers' => [
                    'token' => $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $dataPix,
            ]);

            if ($response->getStatusCode() !== 201) {
                throw new \Exception('Não foi possível criar a cobrança do PIX');
            }

            $responseBody = json_decode($response->getBody()->getContents());

            $cobrancaValidade = new Carbon($responseBody->calendario->criacao, new DateTimeZone('America/Sao_Paulo'));
            $cobrancaValidade->addSeconds($responseBody->calendario->expiracao);

            return [
                'txid' => $responseBody->txid,
                'qrcode' => $responseBody->qrcode,
                'expiresAt' => $cobrancaValidade,
                'value' => $responseBody->valor->original,
                'companyId' => $data['company']['id'] ?? 1,
                'currentDate' => $data['currentDate'] ?? Carbon::now(),
                'renewalDate' => $data['renewalDate'] ?? Carbon::now(),
                'numberOfMonths' => $data['numberOfMonths'] ?? 1,
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao criar cobrança PIX: ' . $e->getMessage());
            throw new \Exception('Erro ao criar cobrança PIX: ' . $e->getMessage());
        }
    }

    /**
     * Checa status de uma cobrança PIX (opcional)
     *
     * @param string $txid
     * @return array|null
     */
    public function checkCobranca(string $txid): ?array
    {
        $client = new Client();

        try {
            $response = $client->get($this->baseUrl . '/' . $txid . '?filter=txid', [
                'headers' => [
                    'token' => $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $body = json_decode($response->getBody()->getContents(), true);

            return $body['data'] ?? null;

        } catch (\Exception $e) {
            Log::error('Erro ao checar cobrança PIX: ' . $e->getMessage());
            return null;
        }
    }
}
