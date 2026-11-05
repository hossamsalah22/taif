<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the Moyasar REST API (https://docs.moyasar.com).
 * Requests authenticate with the secret key as the basic-auth username and an empty password.
 */
class MoyasarService
{
    /**
     * Create a hosted invoice; the parent pays it on the returned `url`.
     *
     * @param  array{amount: int, currency: string, description: string, callback_url?: string, success_url?: string, back_url?: string, expired_at?: string, metadata?: array<string, scalar>}  $attributes
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function createInvoice(array $attributes): array
    {
        return $this->client()->post('invoices', $attributes)->throw()->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function fetchInvoice(string $invoiceId): array
    {
        return $this->client()->get('invoices/'.rawurlencode($invoiceId))->throw()->json();
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.moyasar.base_url'), '/').'/')
            ->withBasicAuth((string) config('services.moyasar.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
