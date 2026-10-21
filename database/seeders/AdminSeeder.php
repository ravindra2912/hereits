<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AdminMember;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\AdminManagenent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        \App\Models\Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'role' => 'superadmin',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'saller@gmail.com'],
            [
                'first_name' => 'Seller',
                'last_name' => 'User',
                'role' => 'Business',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'first_name' => 'Customer',
                'last_name' => 'User',
                'role' => 'User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
