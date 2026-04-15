<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AdminMember;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\AdminManagenent;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Models\Country;
use App\Models\Expert;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $businessSubCategories = [

            // Food & Beverage
            'restaurant',
            'cafe',
            'fast_food',
            'bakery',
            'sweet_shop',
            'cloud_kitchen',
            'catering_service',
            'food_truck',

            // Beauty & Wellness
            'salon',
            'beauty_parlour',
            'spa',
            'barber_shop',
            'makeup_artist',
            'massage_center',

            // Healthcare & Medical
            'clinic',
            'dental_clinic',
            'physiotherapy_center',
            'diagnostic_lab',
            'pathology_lab',
            'pharmacy',
            'veterinary_clinic',

            // Retail & Shops
            'grocery_store',
            'supermarket',
            'clothing_store',
            'electronics_store',
            'mobile_shop',
            'furniture_store',
            'gift_shop',
            'book_store',

            // E-commerce & Online
            'online_store',
            'home_based_seller',
            'dropshipping_business',
            'wholesale_business',

            // Services & Professionals
            'electrician',
            'plumber',
            'carpenter',
            'ac_repair_service',
            'home_cleaning_service',
            'pest_control_service',
            'it_services',
            'digital_marketing_agency',

            // Education & Training
            'school',
            'coaching_institute',
            'tuition_classes',
            'online_course_provider',
            'skill_training_center',
            'music_academy',
            'dance_academy',

            // Real Estate & Property
            'real_estate_agency',
            'property_dealer',
            'builder',
            'rental_services',

            // Automobile & Transport
            'car_service_center',
            'bike_service_center',
            'car_wash',
            'driving_school',
            'taxi_service',

            // Travel, Stay & Events
            'hotel',
            'guest_house',
            'travel_agency',
            'event_planner',
            'wedding_planner',

            // Fitness & Sports
            'gym',
            'yoga_center',
            'fitness_studio',
            'sports_academy',

            // Creative & Media
            'photography_studio',
            'videography_service',
            'graphic_design_studio',
            'printing_press',

        ];

        foreach ($businessSubCategories as $businessSubCategory) {
            BusinessCategory::create([
                'name' => Str::title(str_replace('_', ' ', $businessSubCategory)),
                'slug' => $businessSubCategory
            ]);
        }

        $insert = new Business();
        $insert->owner_id = 1000;
        $insert->name = 'clinic';
        $insert->slug = 'clinic';
        $insert->contact = '9876543210';
        $insert->city_id = 1041;
        $insert->state_id = 12;
        $insert->country_id = 101;
        $insert->area = 'vesu';
        $insert->pincode = '395007';
        $insert->business_category_id = BusinessCategory::first()->id;
        $insert->address = 'vesu suart';
        $insert->longitude = 72.2345;
        $insert->latitude = 102.2345;
        $insert->rating = 4.5;
        $insert->save();

        BusinessSetting::create([
            'business_id' => 1000,
            'is_appointment_system' => 1,
            'is_appointment_with_department' => 1,
            'is_appointment_price_required' => 1,
            'is_ecommerce_system' => 1,
            'is_service_system' => 1,
            // 'is_education_system' => 1,
            // 'is_real_estate_system' => 1,
            // 'is_automobile_system' => 1,
            // 'is_travel_system' => 1,
            // 'is_fitness_system' => 1,
            // 'is_creative_system' => 1,
        ]);
    }
}
