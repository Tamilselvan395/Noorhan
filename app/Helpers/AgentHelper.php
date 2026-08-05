<?php

namespace App\Helpers;

class AgentHelper
{
    /** @return array{browser: string, platform: string, device: string} */
    public static function parse(?string $ua): array
    {
        $ua ??= '';

        $browser = match (true) {
            str_contains($ua, 'Edg')     => 'Edge',
            str_contains($ua, 'OPR')     => 'Opera',
            str_contains($ua, 'Chrome')  => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari')  => 'Safari',
            default                      => 'Unknown Browser',
        };

        $platform = match (true) {
            str_contains($ua, 'Windows')              => 'Windows',
            str_contains($ua, 'Macintosh')            => 'macOS',
            str_contains($ua, 'Android')              => 'Android',
            str_contains($ua, 'iPhone')               => 'iOS',
            str_contains($ua, 'iPad')                 => 'iPadOS',
            str_contains($ua, 'Linux')                => 'Linux',
            default                                   => 'Unknown OS',
        };

        $device = match (true) {
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'Tablet',
            str_contains($ua, 'Mobile') || str_contains($ua, 'iPhone') || str_contains($ua, 'Android') => 'Mobile',
            default => 'Desktop',
        };

        return ['browser' => $browser, 'platform' => $platform, 'device' => $device];
    }
}