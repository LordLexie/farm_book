<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $path    = database_path('seeders/csv/taxes.csv');
        $rows    = array_map('str_getcsv', file($path));
        $headers = array_shift($rows);

        $records = array_map(function ($row) use ($headers) {
            $data               = array_combine($headers, $row);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            return $data;
        }, $rows);

        DB::table('taxes')->insertOrIgnore($records);
    }
}
