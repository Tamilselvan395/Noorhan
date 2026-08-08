<?php

namespace App\Services\Accounting;

use App\Exceptions\ZohoApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ZohoBooksClient
{
    public function __construct(private ZohoTokenService $tokens) {}

    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, ['query' => $query]);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, ['body' => $body]);
    }

    public function put(string $path, array $body = []): array
    {
        return $this->request('put', $path, ['body' => $body]);
    }

    /** Test the connection: GET /organizations */
    public function testConnection(): array
    {
        return $this->get('/organizations');
    }

    private function request(string $method, string $path, array $options, bool $retried = false): array
    {
        $token = $this->tokens->accessToken();

        $pending = Http::withToken($token)
            ->baseUrl(config('zoho.api_url'))
            ->acceptJson();

        if ($method === 'get') {
            $response = $pending->get($path, array_merge($options['query'], ['organization_id' => config('zoho.organization_id')]));
        } else {
            // Zoho Books v3 convention: payload sent as JSONString form parameter.
            $response = $pending->{$method}($path, [
                'organization_id' => config('zoho.organization_id'),
                'JSONString' => json_encode($options['body']),
            ]);
        }

        // Token expired mid-flight → refresh once and retry.
        if ($response->status() === 401 && ! $retried) {
            return $this->request($method, $path, $options, true);
        }

        $this->guard($response);

        return $response->json() ?? [];
    }

    private function guard(Response $response): void
    {
        if ($response->failed()) {
            throw new ZohoApiException(
                'Zoho Books API error ('.$response->status().'): '.($response->json('message') ?? $response->body()),
                $response->json() ?? [],
            );
        }
    }
}