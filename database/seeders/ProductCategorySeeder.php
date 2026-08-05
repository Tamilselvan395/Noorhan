<?php

namespace Database\Seeders;

use App\Enums\Division;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            Division::Automotive->value => ['Brake System', 'Filters', 'Batteries', 'Suspension', 'Spark Plugs', 'Belts & Hoses'],
            Division::Swiftec->value    => ['Engine Oil', 'Grease', 'Hydraulic Oil', 'Coolant'],
            Division::Wiperex->value    => ['Wiper Blades', 'Cleaning Liquid', 'Car Sponge'],
            Division::Otozaar->value    => ['Car Service', 'Car Wash', 'Detailing'],
        ];

        foreach ($catalog as $division => $names) {
            foreach ($names as $i => $name) {
                ProductCategory::updateOrCreate(
                    ['name' => $name, 'division' => $division],
                    ['is_active' => true, 'sort' => $i * 10],
                );
            }
        }
    }
}