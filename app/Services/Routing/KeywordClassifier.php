<?php

namespace App\Services\Routing\Classification;

use App\Contracts\LeadClassifierInterface;
use App\DTOs\Routing\ClassificationResult;
use App\Models\Lead;

class KeywordClassifier implements LeadClassifierInterface
{
    private const BRAND_KEYWORDS = [
        'japanese'  => ['toyota', 'nissan', 'honda', 'mazda', 'mitsubishi', 'subaru', 'lexus', 'suzuki', 'isuzu'],
        'american'  => ['ford', 'chevrolet', 'chevy', 'gmc', 'dodge', 'ram ', 'jeep', 'cadillac', 'lincoln', 'buick'],
        'european'  => ['bmw', 'mercedes', 'benz', 'audi', 'volkswagen', 'vw ', 'volvo', 'renault', 'peugeot', 'citroen', 'opel', 'porsche', 'land rover'],
        'korean'    => ['hyundai', 'kia', 'ssangyong', 'daewoo'],
    ];

    private const DIVISION_KEYWORDS = [
        'swiftec'    => ['engine oil', 'lubricant', 'grease', 'hydraulic oil', 'coolant', 'antifreeze'],
        'wiperex'    => ['wiper', 'blade', 'sponge', 'cleaning liquid', 'washer fluid'],
        'otozaar'    => ['car service', 'repair', 'maintenance', 'car wash', 'oil change', 'installation'],
        'automotive' => ['spare part', 'brake pad', 'filter', 'battery', 'suspension', 'clutch'],
    ];

    private const TYPE_KEYWORDS = [
        'distributor'     => ['distributor', 'distribution'],
        'dealer'          => ['dealer', 'dealership'],
        'garage'          => ['garage'],
        'workshop'        => ['workshop'],
        'auto_parts_shop' => ['parts shop', 'auto shop', 'spare parts shop'],
        'corporate'       => ['fleet', 'corporate', 'tender'],
        'retail'          => ['retail', 'personal car'],
    ];

    public function classify(Lead $lead): ClassificationResult
    {
        $text = strtolower(trim(($lead->subject ?? '').' '.($lead->requirements ?? '')));

        if ($text === '') {
            return ClassificationResult::empty();
        }

        $brand = $this->firstMatch($text, self::BRAND_KEYWORDS);
        $division = $this->firstMatch($text, self::DIVISION_KEYWORDS);
        $type = $this->firstMatch($text, self::TYPE_KEYWORDS);

        $hits = ($brand ? 1 : 0) + ($division ? 1 : 0) + ($type ? 1 : 0);

        $confidence = match (true) {
            $hits >= 2 => 0.9,
            $hits === 1 => 0.6,
            default => 0.0,
        };

        return new ClassificationResult($brand, $division, $type, $confidence);
    }

    /** @param array<string, array<int, string>> $map */
    private function firstMatch(string $text, array $map): ?string
    {
        foreach ($map as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }
}