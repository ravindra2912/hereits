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

        \App\Models\Admin::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'role' => 'superadmin',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Seller',
            'last_name' => 'User',
            'email' => 'saller@gmail.com',
            'role' => 'Business',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Customer',
            'last_name' => 'User',
            'email' => 'customer@gmail.com',
            'role' => 'User',
            'password' => Hash::make('password'),
        ]);
    }
}
