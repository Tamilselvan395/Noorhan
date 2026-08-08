<?php

namespace App\Services\Accounting;

use App\Models\ZohoConnection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZohoTokenService
{
    public function connection(): ?ZohoConnection
    {
        return ZohoConnection::query()->first();
    }

    public function requireConnection(): ZohoConnection
    {
        return $this->connection() ?? throw new RuntimeException('Zoho Books is not connected.');
    }

    public function accessToken(): string
    {
        $connection = $this->requireConnection();

        if ($connection->access_token_cipher && $connection->token_expires_at?->isFuture()) {
            return Crypt::decryptString($connection->access_token_cipher);
        }

        return $this->refresh();
    }

    /** Exchange OAuth authorization code for tokens (callback step). */
    public function storeFromCode(string $code, string $organizationId): ZohoConnection
    {
        $response = Http::asForm()->post(config('zoho.accounts_url').'/oauth/v2/token', [
            'code' => $code,
            'client_id' => config('zoho.client_id'),
            'client_secret' => config('zoho.client_secret'),
            'redirect_uri' => config('zoho.redirect_uri'),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Zoho token exchange failed: '.$response->body());
        }

        $data = $response->json();

        return ZohoConnection::updateOrCreate(
            ['organization_id' => $organizationId],
            [
                'client_id' => config('zoho.client_id'),
                'client_secret_cipher' => Crypt::encryptString((string) config('zoho.client_secret')),
                'refresh_token_cipher' => Crypt::encryptString($data['refresh_token']),
                'access_token_cipher' => Crypt::encryptString($data['access_token']),
                'token_expires_at' => now()->addSeconds(((int) $data['expires_in']) - 60),
            ],
        );
    }

    public function refresh(): string
    {
        $connection = $this->requireConnection();

        $response = Http::asForm()->post(config('zoho.accounts_url').'/oauth/v2/token', [
            'refresh_token' => Crypt::decryptString($connection->refresh_token_cipher),
            'client_id' => $connection->client_id,
            'client_secret' => Crypt::decryptString($connection->client_secret_cipher),
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Zoho token refresh failed: '.$response->body());
        }

        $data = $response->json();

        $connection->update([
            'access_token_cipher' => Crypt::encryptString($data['access_token']),
            'token_expires_at' => now()->addSeconds(((int) $data['expires_in']) - 60),
        ]);

        return $data['access_token'];
    }
}