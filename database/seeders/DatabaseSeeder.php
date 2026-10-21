<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //for production
        if (app()->environment('production')) {
            $this->call(SiteSettingSeeder::class);
            $this->call(AdminSeeder::class);
            $this->call(LegalPageSeeder::class);
            $this->call(BusinessSeeder::class);
            $this->call(PlanSeeder::class);
            $this->call(FaqSeeder::class);
        } else {
            $this->call(SiteSettingSeeder::class);
            $this->call(AdminSeeder::class);
            $this->call(LegalPageSeeder::class);
            $this->call(BusinessSeeder::class);
            $this->call(CategorySeeder::class);
            $this->call(BlogSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(ServiceSeeder::class);
            $this->call(PlanSeeder::class);
            $this->call(AppointmentDepartmentSeeder::class);
            $this->call(ExpertSeeder::class);
            $this->call(FaqSeeder::class);
        }


        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
