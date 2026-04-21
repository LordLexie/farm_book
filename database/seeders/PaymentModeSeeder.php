<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentModeSeeder extends Seeder
{
    public function run(): void
    {
        $path    = database_path('seeders/csv/payment_modes.csv');
        $rows    = array_map('str_getcsv', file($path));
        $headers = array_shift($rows);

        $records = array_map(function ($row) use ($headers) {
            $data               = array_combine($headers, $row);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            return $data;
        }, $rows);

        DB::table('payment_modes')->insertOrIgnore($records);
    }
}
