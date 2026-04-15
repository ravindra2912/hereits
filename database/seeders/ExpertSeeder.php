<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessTiming;
use App\Models\Expert;
use App\Models\AppointmentDepartment;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ExpertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expert::create([
            'business_id' => 1000,
            'department_id' => 1,
            'expert_name' => 'clinic',
            'slug' => 'expert',
            'email' => 'expert@gmail.com',
            'is_appointment_book_with_time_slot' => false,
            'is_need_booking_confirmation' => false,
            'is_default' => true,
            'password' => Hash::make('password')
        ]);
    }
}
