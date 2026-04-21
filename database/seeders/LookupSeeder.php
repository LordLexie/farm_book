<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('statuses')->insertOrIgnore([
            ['code' => 'ACT',       'name' => 'Active',    'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'INACTIVE',  'name' => 'Inactive',  'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'DRAFT',     'name' => 'Draft',     'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PENDING',   'name' => 'Pending',   'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PAID',      'name' => 'Paid',      'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PARTIAL',   'name' => 'Partial',   'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CANCELLED', 'name' => 'Cancelled', 'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'OVERDUE',   'name' => 'Overdue',   'category' => 'GEN',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ALIVE',     'name' => 'Alive',     'category' => 'LIVE', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SOLD',      'name' => 'Sold',      'category' => 'LIVE', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'DECEASED',  'name' => 'Deceased',  'category' => 'LIVE', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('rate_plans')->insertOrIgnore([
            ['code' => 'STANDARD',  'name' => 'Standard',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PREMIUM',   'name' => 'Premium',   'created_at' => now(), 'updated_at' => now()],
            ['code' => 'WHOLESALE', 'name' => 'Wholesale', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('service_types')->insertOrIgnore([
            ['code' => 'VET',         'name' => 'Veterinary',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'TRANSPORT',   'name' => 'Transport',        'created_at' => now(), 'updated_at' => now()],
            ['code' => 'MAINTENANCE', 'name' => 'Maintenance',      'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CONSULTING',  'name' => 'Consulting',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'OTHER',       'name' => 'Other',            'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('item_categories')->insertOrIgnore([
            ['code' => 'FEED',      'name' => 'Animal Feed',    'created_at' => now(), 'updated_at' => now()],
            ['code' => 'MEDICINE',  'name' => 'Medicine',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EQUIPMENT', 'name' => 'Equipment',      'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SUPPLIES',  'name' => 'Supplies',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'OTHER',     'name' => 'Other',          'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('unit_of_measures')->insertOrIgnore([
            ['code' => 'KG',    'name' => 'Kilogram',  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'G',     'name' => 'Gram',      'created_at' => now(), 'updated_at' => now()],
            ['code' => 'L',     'name' => 'Litre',     'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ML',    'name' => 'Millilitre','created_at' => now(), 'updated_at' => now()],
            ['code' => 'PCS',   'name' => 'Pieces',    'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BAG',   'name' => 'Bag',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BOX',   'name' => 'Box',       'created_at' => now(), 'updated_at' => now()],
            ['code' => 'HRS',   'name' => 'Hours',     'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('farm_session_types')->insertOrIgnore([
            ['code' => 'MORNING',   'name' => 'Morning',   'created_at' => now(), 'updated_at' => now()],
            ['code' => 'AFTERNOON', 'name' => 'Afternoon', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EVENING',   'name' => 'Evening',   'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
