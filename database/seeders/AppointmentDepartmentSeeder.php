<?php

namespace Database\Seeders;

use App\Models\AppointmentDepartment;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AppointmentDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $departments = [
            'Cardiology',
            'Dermatology',
            'Neurology',
            'Orthopedics',
            'Pediatrics',
            'Psychiatry',
            'Ophthalmology',
            'Dentistry',
            'General Practice',
            'Gynecology',
            'Urology',
            'Rheumatology',
            'Gastroenterology',
            'Pulmonology',
            'Hematology',
            'Oncology',
            'Nephrology',
            'Endocrinology',
            'Immunology',
            'Physical Therapy',
        ];

        foreach ($departments as $department) {
            AppointmentDepartment::create([
                'department_name' => $department,
                'business_id' => 1000,
            ]);
        }
    }
}
