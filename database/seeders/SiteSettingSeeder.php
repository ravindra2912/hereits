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
        if (!SiteSetting::first()) {
            SiteSetting::create([
                'per_credit_price' => 1,
                'free_credit' => 30,
                'charge_place_order_on_website' => 0.1,
                'charge_place_order_on_pos' => 0.05,
                'draw_credit_per_scratch' => 1.00,
                'charge_per_quotation' => 1.00,
                'payment_gateway' => 'cashfree',
            ]);
        }

        if (DB::table('countries')->count() === 0) {
            $countries = database_path('sql/countries.sql');
            if (File::exists($countries)) {
                DB::unprepared(File::get($countries));
            }
        }

        if (DB::table('states')->count() === 0) {
            $states = database_path('sql/states.sql');
            if (File::exists($states)) {
                DB::unprepared(File::get($states));
            }
        }

        if (DB::table('cities')->count() === 0) {
            $cities = database_path('sql/cities.sql');
            if (File::exists($cities)) {
                DB::unprepared(File::get($cities));
            }
        }
    }
}
