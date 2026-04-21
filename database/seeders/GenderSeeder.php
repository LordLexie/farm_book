<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/csv/genders.csv');
        $rows = array_map('str_getcsv', file($path));
        $headers = array_shift($rows);

        $records = array_map(function ($row) use ($headers) {
            $data = array_combine($headers, $row);
            $data['created_at'] = now();
            $data['updated_at'] = now();
            return $data;
        }, $rows);

        DB::table('genders')->insertOrIgnore($records);
    }
}
