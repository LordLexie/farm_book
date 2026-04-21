<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LookupSeeder::class,
            GenderSeeder::class,
            CurrencySeeder::class,
            BillingCycleSeeder::class,
            ItemCategorySeeder::class,
            ServiceTypeSeeder::class,
            LivestockTypeSeeder::class,
            FarmSessionTemplateSeeder::class,
            PaymentModeSeeder::class,
            TaxSeeder::class,
            UserSeeder::class,
        ]);
    }
}
