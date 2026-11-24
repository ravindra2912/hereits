<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessInquiry;
use App\Models\BusinessInquiryLog;
use App\Models\BusinessInquirySource;
use App\Models\BusinessSetting;
use App\Models\CreditHistory;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessInquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::with(['owner', 'businessSetting'])->get();

        if ($businesses->isEmpty()) {
            $this->command?->warn('No businesses found to seed inquiries for.');
            return;
        }

        foreach ($businesses as $business) {
            $owner = $business->owner;
            $ownerId = $owner?->id;

            // 1. Ensure business has credits in BusinessSetting so vendor can test acceptance
            $setting = BusinessSetting::firstOrCreate(
                ['business_id' => $business->id],
                [
                    'credit' => 25.00,
                    'is_inquiry_charge_manual' => false,
                    'charge_per_contact_us_inquiry' => 1.00,
                    'charge_per_product_inquiry' => 1.00,
                    'charge_per_service_inquiry' => 1.00,
                ]
            );

            if ($setting->credit < 15.00) {
                $setting->update(['credit' => 25.00]);
            }

            // Get some products and services belonging to this business (or any fallback)
            $products = Product::where('business_id', $business->id)->take(5)->get();
            if ($products->isEmpty()) {
                $products = Product::take(5)->get();
            }

            $services = Service::where('business_id', $business->id)->take(5)->get();
            if ($services->isEmpty()) {
                $services = Service::take(5)->get();
            }

            // Clear previous test inquiries for clean re-seeding if re-run
            // BusinessInquiry::where('business_id', $business->id)->delete();

            $now = Carbon::now();

            // Sample inquiries dataset with varied statuses
            $sampleInquiries = [
                // 1. Pending Contact Us Inquiry
                [
                    'name'            => 'Rahul Sharma',
                    'contact'         => '9820123456',
                    'email'           => 'rahul.sharma@example.com',
                    'message'         => 'Hello, I would like to inquire about your business timings and whether you offer bulk commercial partnerships.',
                    'inquiry_type'    => BusinessInquiry::TYPE_CONTACT_US,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_PENDING,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => false,
                    'sources'         => [],
                    'created_at'      => $now->copy()->subMinutes(25),
                ],

                // 2. Pending Product Inquiry
                [
                    'name'            => 'Priya Patel',
                    'contact'         => '9879154321',
                    'email'           => 'priya.patel@gmail.com',
                    'message'         => 'Hi, is this product available in stock for immediate delivery to Navrangpura? Please provide best price for 2 units.',
                    'inquiry_type'    => BusinessInquiry::TYPE_PRODUCT,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_PENDING,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => false,
                    'sources'         => $products->isNotEmpty() ? [['source_id' => $products->first()->id, 'source_type' => 'product']] : [],
                    'created_at'      => $now->copy()->subHours(2),
                ],

                // 3. Pending Service Inquiry
                [
                    'name'            => 'Amitabh Verma',
                    'contact'         => '9904231876',
                    'email'           => 'amitabh.verma@outlook.com',
                    'message'         => 'I am looking for a complete consultation package this weekend. Please let me know the available time slots and fees.',
                    'inquiry_type'    => BusinessInquiry::TYPE_SERVICE,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_PENDING,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => false,
                    'sources'         => $services->isNotEmpty() ? [['source_id' => $services->first()->id, 'source_type' => 'service']] : [],
                    'created_at'      => $now->copy()->subHours(5),
                ],

                // 4. Accepted - Newly Accepted (new)
                [
                    'name'            => 'Vikram Mehta',
                    'contact'         => '9825098765',
                    'email'           => 'vikram.mehta@yahoo.com',
                    'message'         => 'Interested in your catalog. Looking forward to connecting.',
                    'inquiry_type'    => BusinessInquiry::TYPE_PRODUCT,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => true,
                    'accepted_at'     => $now->copy()->subHours(3),
                    'sources'         => $products->count() > 1 ? [['source_id' => $products->skip(1)->first()->id, 'source_type' => 'product']] : [],
                    'created_at'      => $now->copy()->subHours(6),
                ],

                // 5. Accepted - Contacted
                [
                    'name'            => 'Sneha Kulkarni',
                    'contact'         => '9724011223',
                    'email'           => 'sneha.k@rediffmail.com',
                    'message'         => 'Requested quote for custom requirements.',
                    'inquiry_type'    => BusinessInquiry::TYPE_SERVICE,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_CONTACTED,
                    'credit_deducted' => true,
                    'accepted_at'     => $now->copy()->subDay(),
                    'contacted_at'    => $now->copy()->subHours(20),
                    'assigned_to'     => $ownerId,
                    'sources'         => $services->count() > 1 ? [['source_id' => $services->skip(1)->first()->id, 'source_type' => 'service']] : [],
                    'created_at'      => $now->copy()->subDays(2),
                ],

                // 6. Accepted - Follow-up Scheduled
                [
                    'name'              => 'Anand Deshmukh',
                    'contact'           => '9988223344',
                    'email'             => 'anand.deshmukh@gmail.com',
                    'message'           => 'Need quotation for 10 units. Call me tomorrow afternoon.',
                    'inquiry_type'      => BusinessInquiry::TYPE_PRODUCT,
                    'system_status'     => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status'   => BusinessInquiry::BUSINESS_STATUS_FOLLOW_UP,
                    'credit_deducted'   => true,
                    'accepted_at'       => $now->copy()->subDays(2),
                    'contacted_at'      => $now->copy()->subDays(2)->addHours(2),
                    'next_follow_up_at' => $now->copy()->addDay()->setHour(14)->setMinute(0),
                    'assigned_to'       => $ownerId,
                    'sources'           => $products->isNotEmpty() ? [['source_id' => $products->first()->id, 'source_type' => 'product']] : [],
                    'created_at'        => $now->copy()->subDays(3),
                ],

                // 7. Accepted - Meeting Scheduled
                [
                    'name'              => 'Deepak Joshi',
                    'contact'           => '9898012345',
                    'email'             => 'deepak.joshi@company.org',
                    'message'           => 'Scheduled an in-person meeting at your office.',
                    'inquiry_type'      => BusinessInquiry::TYPE_CONTACT_US,
                    'system_status'     => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status'   => BusinessInquiry::BUSINESS_STATUS_SCHEDULED,
                    'credit_deducted'   => true,
                    'accepted_at'       => $now->copy()->subDays(3),
                    'contacted_at'      => $now->copy()->subDays(3)->addHours(4),
                    'next_follow_up_at' => $now->copy()->addDays(2)->setHour(11)->setMinute(30),
                    'assigned_to'       => $ownerId,
                    'sources'           => [],
                    'created_at'        => $now->copy()->subDays(4),
                ],

                // 8. Accepted - Converted
                [
                    'name'            => 'Pooja Trivedi',
                    'contact'         => '9712345678',
                    'email'           => 'pooja.trivedi@gmail.com',
                    'message'         => 'Confirmed order and made full payment via online transfer.',
                    'inquiry_type'    => BusinessInquiry::TYPE_PRODUCT,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_CONVERTED,
                    'credit_deducted' => true,
                    'accepted_at'     => $now->copy()->subDays(5),
                    'contacted_at'    => $now->copy()->subDays(5)->addHours(1),
                    'converted_at'    => $now->copy()->subDays(4),
                    'assigned_to'     => $ownerId,
                    'sources'         => $products->isNotEmpty() ? [['source_id' => $products->first()->id, 'source_type' => 'product']] : [],
                    'created_at'      => $now->copy()->subDays(6),
                ],

                // 9. Accepted - Closed
                [
                    'name'            => 'Karan Malhotra',
                    'contact'         => '9822001122',
                    'email'           => 'karan.m@gmail.com',
                    'message'         => 'Customer decided to postpone purchase for 6 months.',
                    'inquiry_type'    => BusinessInquiry::TYPE_SERVICE,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_CLOSED,
                    'credit_deducted' => true,
                    'accepted_at'     => $now->copy()->subDays(7),
                    'contacted_at'    => $now->copy()->subDays(7)->addHours(3),
                    'closed_at'       => $now->copy()->subDays(6),
                    'assigned_to'     => $ownerId,
                    'sources'         => $services->isNotEmpty() ? [['source_id' => $services->first()->id, 'source_type' => 'service']] : [],
                    'created_at'      => $now->copy()->subDays(8),
                ],

                // 10. Rejected Inquiry
                [
                    'name'            => 'Suresh Raina',
                    'contact'         => '9925114477',
                    'email'           => 'suresh.raina@test.com',
                    'message'         => 'Deliver to remote location outside standard coverage area.',
                    'inquiry_type'    => BusinessInquiry::TYPE_CONTACT_US,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_REJECTED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => false,
                    'sources'         => [],
                    'created_at'      => $now->copy()->subDays(4),
                ],

                // 11. Not Fulfilled Inquiry
                [
                    'name'            => 'Nisha Shah',
                    'contact'         => '9898771122',
                    'email'           => 'nisha.shah@gmail.com',
                    'message'         => 'Requested vintage discontinued item which is not available.',
                    'inquiry_type'    => BusinessInquiry::TYPE_PRODUCT,
                    'system_status'   => BusinessInquiry::SYSTEM_STATUS_NOT_FULFILLED,
                    'business_status' => BusinessInquiry::BUSINESS_STATUS_NEW,
                    'credit_deducted' => false,
                    'sources'         => $products->isNotEmpty() ? [['source_id' => $products->first()->id, 'source_type' => 'product']] : [],
                    'created_at'      => $now->copy()->subDays(5),
                ],
            ];

            foreach ($sampleInquiries as $item) {
                $sources = $item['sources'];
                unset($item['sources']);

                $item['business_id'] = $business->id;

                $creditHistoryId = null;
                if ($item['credit_deducted']) {
                    // Create debit credit history record
                    $creditHistory = CreditHistory::create([
                        'business_id'      => $business->id,
                        'transaction_type' => 'debit',
                        'type'             => 'inquiry',
                        'amount'           => 1.00,
                        'balance_after'    => max(0.00, $setting->credit - 1.00),
                        'description'      => 'Credit deducted for accepting inquiry from ' . $item['name'],
                        'created_at'       => $item['accepted_at'] ?? $now,
                        'updated_at'       => $item['accepted_at'] ?? $now,
                    ]);
                    $creditHistoryId = $creditHistory->id;
                }

                $item['credit_deducted_id'] = $creditHistoryId;

                $inquiry = BusinessInquiry::create($item);

                // Attach source items
                foreach ($sources as $source) {
                    BusinessInquirySource::create([
                        'inquiry_id'  => $inquiry->id,
                        'source_id'   => $source['source_id'],
                        'source_type' => $source['source_type'],
                        'created_at'  => $inquiry->created_at,
                        'updated_at'  => $inquiry->created_at,
                    ]);
                }

                // Initial creation log
                BusinessInquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id'    => null,
                    'action'     => 'created',
                    'old_value'  => null,
                    'new_value'  => 'pending',
                    'remarks'    => 'Inquiry submitted from frontend website',
                    'created_at' => $inquiry->created_at,
                    'updated_at' => $inquiry->created_at,
                ]);

                // Acceptance log
                if ($inquiry->isAccepted()) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'accepted',
                        'old_value'  => 'pending',
                        'new_value'  => 'accepted',
                        'remarks'    => 'Inquiry accepted. Deducted 1 credit.',
                        'created_at' => $inquiry->accepted_at ?? $now,
                        'updated_at' => $inquiry->accepted_at ?? $now,
                    ]);

                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'business_status_changed',
                        'old_value'  => null,
                        'new_value'  => 'new',
                        'remarks'    => 'Inquiry entered sales pipeline',
                        'created_at' => $inquiry->accepted_at ?? $now,
                        'updated_at' => $inquiry->accepted_at ?? $now,
                    ]);
                }

                // Contacted log
                if ($inquiry->contacted_at) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'business_status_changed',
                        'old_value'  => 'new',
                        'new_value'  => 'contacted',
                        'remarks'    => 'Reached customer on phone/WhatsApp',
                        'created_at' => $inquiry->contacted_at,
                        'updated_at' => $inquiry->contacted_at,
                    ]);
                }

                // Follow-up scheduled log
                if ($inquiry->next_follow_up_at) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'follow_up_updated',
                        'old_value'  => null,
                        'new_value'  => $inquiry->next_follow_up_at->toIso8601String(),
                        'remarks'    => 'Scheduled next conversation',
                        'created_at' => $inquiry->contacted_at ?? $inquiry->accepted_at ?? $now,
                        'updated_at' => $inquiry->contacted_at ?? $inquiry->accepted_at ?? $now,
                    ]);
                }

                // Converted log
                if ($inquiry->converted_at) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'business_status_changed',
                        'old_value'  => 'contacted',
                        'new_value'  => 'converted',
                        'remarks'    => 'Customer agreed to proposal and placed order',
                        'created_at' => $inquiry->converted_at,
                        'updated_at' => $inquiry->converted_at,
                    ]);
                }

                // Closed log
                if ($inquiry->closed_at) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'business_status_changed',
                        'old_value'  => 'contacted',
                        'new_value'  => 'closed',
                        'remarks'    => 'Customer not interested at this time',
                        'created_at' => $inquiry->closed_at,
                        'updated_at' => $inquiry->closed_at,
                    ]);
                }

                // Rejected log
                if ($inquiry->isRejected()) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'rejected',
                        'old_value'  => 'pending',
                        'new_value'  => 'rejected',
                        'remarks'    => 'Outside servicing area',
                        'created_at' => $inquiry->created_at->addHour(),
                        'updated_at' => $inquiry->created_at->addHour(),
                    ]);
                }

                // Not fulfilled log
                if ($inquiry->isNotFulfilled()) {
                    BusinessInquiryLog::create([
                        'inquiry_id' => $inquiry->id,
                        'user_id'    => $ownerId,
                        'action'     => 'not_fulfilled',
                        'old_value'  => 'pending',
                        'new_value'  => 'not_fulfilled',
                        'remarks'    => 'Requested item is discontinued',
                        'created_at' => $inquiry->created_at->addHour(),
                        'updated_at' => $inquiry->created_at->addHour(),
                    ]);
                }
            }
        }
    }
}

