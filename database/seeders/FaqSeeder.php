<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('faqs')->insert([

            // ---------------- GENERAL ----------------
            [
                'type' => 'General',
                'question' => 'What is Hereits?',
                'answer' => 'Hereits is a business management platform that helps you manage appointments, list products and services, and connect with customers online from one place.',
            ],
            [
                'type' => 'General',
                'question' => 'Who can use Hereits?',
                'answer' => 'Hereits is designed for small and medium businesses such as salons, clinics, service providers, consultants, and local stores.',
            ],
            [
                'type' => 'General',
                'question' => 'Do I need technical knowledge to use Hereits?',
                'answer' => 'No, Hereits is built to be simple and easy to use. Anyone can get started without technical skills.',
            ],
            [
                'type' => 'General',
                'question' => 'Is Hereits free to use?',
                'answer' => 'Hereits offers affordable plans with usage-based pricing. Some features may require a paid subscription.',
            ],

            // ---------------- BUSINESS ----------------
            [
                'type' => 'Business',
                'question' => 'How do I register my business on Hereits?',
                'answer' => 'You can register by signing up on Hereits, adding your business details, and completing basic verification.',
            ],
            [
                'type' => 'Business',
                'question' => 'Can I manage multiple businesses with one account?',
                'answer' => 'Yes, Hereits allows you to manage multiple businesses depending on your selected plan.',
            ],
            [
                'type' => 'Business',
                'question' => 'Can I add multiple experts or staff members?',
                'answer' => 'Yes, you can add and manage multiple experts or staff members based on your subscription plan.',
            ],
            [
                'type' => 'Business',
                'question' => 'Is my business information visible to customers?',
                'answer' => 'Only the information you choose to publish will be visible to customers.',
            ],

            // ---------------- APPOINTMENT ----------------
            [
                'type' => 'Appointment',
                'question' => 'How does appointment booking work?',
                'answer' => 'Customers can book available time slots, and the system automatically manages confirmations, queues, and notifications.',
            ],
            [
                'type' => 'Appointment',
                'question' => 'Can I manage appointment queues?',
                'answer' => 'Yes, Hereits supports smart queue management to handle high-demand periods.',
            ],
            [
                'type' => 'Appointment',
                'question' => 'What happens if a customer does not show up?',
                'answer' => 'You can mark the appointment as a no-show, and the system can move the next customer from the queue.',
            ],
            [
                'type' => 'Appointment',
                'question' => 'Can experts manage appointments themselves?',
                'answer' => 'Yes, experts have their own panel to manage daily appointments easily.',
            ],

            // ---------------- SERVICES ----------------
            [
                'type' => 'Services',
                'question' => 'Can I list multiple services on Hereits?',
                'answer' => 'Yes, you can list multiple services along with pricing and descriptions.',
            ],
            [
                'type' => 'Services',
                'question' => 'Can I temporarily disable a service?',
                'answer' => 'Yes, services can be enabled or disabled at any time from your dashboard.',
            ],
            [
                'type' => 'Services',
                'question' => 'Can I update service prices?',
                'answer' => 'Yes, you can update service details and pricing anytime.',
            ],

            // ---------------- PRODUCT ----------------
            [
                'type' => 'Product',
                'question' => 'Can I list my products on Hereits?',
                'answer' => 'Yes, you can add and display your products with images, prices, and descriptions.',
            ],
            [
                'type' => 'Product',
                'question' => 'Is there a limit on product listings?',
                'answer' => 'Product limits depend on your subscription plan. Some plans include free product listings.',
            ],
            [
                'type' => 'Product',
                'question' => 'Can customers order products directly?',
                'answer' => 'Product ordering depends on the features enabled for your business.',
            ],
            [
                'type' => 'Product',
                'question' => 'Can I edit or remove products later?',
                'answer' => 'Yes, you can edit or remove product listings anytime.',
            ],

        ]);
    }
}
