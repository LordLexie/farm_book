<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LivestockTypeSeeder extends Seeder
{
    public function run(): void
    {
        $path    = database_path('seeders/csv/livestock_types.csv');
        $rows    = array_map('str_getcsv', file($path));
        $headers = array_shift($rows);

        $records = array_map(function ($row) use ($headers) {
            $data               = array_combine($headers, $row);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            return $data;
        }, $rows);

        DB::table('livestock_types')->insertOrIgnore($records);
    }
}
