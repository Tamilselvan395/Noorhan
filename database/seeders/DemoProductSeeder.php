<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Brake System', 'automotive', 'SW-BP-001', 'SW Front Brake Pads — Japanese Fitment', 45.00, 89.00, ['fitment' => 'Toyota / Nissan / Honda']],
            ['Filters', 'automotive', 'SW-OF-010', 'SW Oil Filter — European Fitment', 12.50, 28.00, ['fitment' => 'BMW / Mercedes / Audi']],
            ['Engine Oil', 'swiftec', 'SF-EO-5W30', 'Swiftec Ultra Engine Oil 5W-30 (4L)', 52.00, 95.00, ['viscosity' => '5W-30', 'base' => 'Synthetic', 'pack' => '4L']],
            ['Grease', 'swiftec', 'SF-GR-002', 'Swiftec MP Lithium Grease (500g)', 8.00, 16.50, ['type' => 'Lithium MP']],
            ['Hydraulic Oil', 'swiftec', 'SF-HY-068', 'Swiftec Hydraulic Oil ISO 68 (20L)', 95.00, 160.00, ['iso' => '68', 'pack' => '20L']],
            ['Coolant', 'swiftec', 'SF-CL-004', 'Swiftec Long-Life Coolant (1L)', 6.50, 14.00, ['color' => 'Green', 'pack' => '1L']],
            ['Wiper Blades', 'wiperex', 'WX-WB-022', 'Wiperex Frameless Wiper Blade 22"', 9.00, 22.00, ['size' => '22"', 'type' => 'Frameless']],
            ['Cleaning Liquid', 'wiperex', 'WX-CL-001', 'Wiperex Windshield Cleaning Liquid (2L)', 3.00, 8.00, ['pack' => '2L']],
            ['Car Sponge', 'wiperex', 'WX-CS-003', 'Wiperex Microfiber Car Sponge', 2.00, 6.00, ['material' => 'Microfiber']],
            ['Car Service', 'otozaar', 'OZ-SRV-001', 'Otozaar Premium Full Service Package', 180.00, 399.00, ['duration' => '3 hrs']],
        ];

        foreach ($items as [$category, $division, $sku, $name, $cost, $sale, $attributes]) {
            Product::updateOrCreate(['sku' => $sku], [
                'name' => $name,
                'category_id' => ProductCategory::where('name', $category)->where('division', $division)->first()?->id,
                'division' => $division,
                'unit' => str_contains($sku, 'SF-') ? 'bottle' : 'pcs',
                'cost_price' => $cost,
                'sale_price' => $sale,
                'tax_rate' => 5.00,
                'attributes' => $attributes,
                'is_active' => true,
            ]);
        }
    }
}