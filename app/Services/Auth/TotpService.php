<?php

namespace App\Services\Auth;

class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 16): string
    {
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[random_int(0, 31)];
        }

        return $secret;
    }

    public function otpauthUri(string $secret, string $account, string $issuer = 'Noorhan CRM'): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer).':'.rawurlencode($account)
            .'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&algorithm=SHA1&digits=6&period=30';
    }

    public function qrUrl(string $secret, string $account): string
    {
        // Swap for a local QR package in air-gapped deployments.
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.rawurlencode($this->otpauthUri($secret, $account));
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s/', '', $code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $now = time();

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->code($secret, $now + ($i * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    public function code(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $counter = intdiv($timestamp, 30);

        $hash = hash_hmac('sha1', pack('N*', 0).pack('N*', $counter), $this->base32Decode($secret), true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $bits = '';

        foreach (str_split(strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret))) as $char) {
            $bits .= str_pad(decbin((int) strpos(self::ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}