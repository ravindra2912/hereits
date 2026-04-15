<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        SiteSetting::create([
            'per_credit_price' => 1,
            'free_product_limit' => 20,
            'free_service_limit' => 10,
            'charge_place_order_on_website' => 0.1,
            'charge_place_order_on_pos' => 0.05,
        ]);

        $countries = database_path('sql/countries.sql');
        if (!File::exists($countries)) {
            echo "SQL file not found.";
            return;
        }
        DB::unprepared(File::get($countries));

        $states = database_path('sql/states.sql');

        if (!File::exists($states)) {
            echo "SQL file not found.";
            return;
        }
        DB::unprepared(File::get($states));


        $cities = database_path('sql/cities.sql');

        if (!File::exists($cities)) {
            echo "SQL file not found.";
            return;
        }
        DB::unprepared(File::get($cities));
    }
}
