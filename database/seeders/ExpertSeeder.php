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
        $business = Business::first();
        $businessId = $business ? $business->id : 1;
        $department = AppointmentDepartment::where('business_id', $businessId)->first();

        $user = \App\Models\User::firstOrCreate(
            ['email' => 'expert@gmail.com'],
            [
                'first_name' => 'Clinic',
                'last_name' => 'Expert',
                'password' => Hash::make('password'),
                'role' => 'Expert',
            ]
        );

        Expert::create([
            'business_id' => $businessId,
            'department_id' => $department ? $department->id : null,
            'user_id' => $user->id,
            'slug' => 'expert',
            'is_appointment_book_with_time_slot' => false,
            'is_need_booking_confirmation' => false,
            'is_default' => true,
        ]);
    }
}
