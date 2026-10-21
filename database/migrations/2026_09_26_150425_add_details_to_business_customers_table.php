<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_customers', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();

            $table->string('business_name')->nullable()->after('customer_role_id');
            $table->string('customer_name')->nullable()->after('business_name');
            $table->string('contact', 50)->nullable()->after('customer_name');
            $table->text('address')->nullable()->after('contact');
            $table->string('state')->nullable()->after('address');
            $table->string('city')->nullable()->after('state');
            $table->string('pincode', 20)->nullable()->after('city');

            $table->index(['business_id', 'contact']);
        });

        // Backfill existing business_customer records using user_id, latest quotation, and order
        DB::table('business_customers')
            ->whereNotNull('customer_id')
            ->orderBy('id')
            ->chunkById(100, function ($records) {
                foreach ($records as $bc) {
                    $user = DB::table('users')->where('id', $bc->customer_id)->first();
                    $bizName = DB::table('businesses')->where('owner_id', $bc->customer_id)->value('name');

                    // Check latest quotation with address for this customer (prefer current business)
                    $quotation = DB::table('quotations')
                        ->where('customer_id', $bc->customer_id)
                        ->where('business_id', $bc->business_id)
                        ->where(function ($q) {
                            $q->whereNotNull('address')->where('address', '!=', '')
                              ->orWhereNotNull('city')->where('city', '!=', '')
                              ->orWhereNotNull('state')->where('state', '!=', '')
                              ->orWhereNotNull('pincode')->where('pincode', '!=', '');
                        })
                        ->latest('id')
                        ->first();

                    if (!$quotation) {
                        $quotation = DB::table('quotations')
                            ->where('customer_id', $bc->customer_id)
                            ->where(function ($q) {
                                $q->whereNotNull('address')->where('address', '!=', '')
                                  ->orWhereNotNull('city')->where('city', '!=', '')
                                  ->orWhereNotNull('state')->where('state', '!=', '')
                                  ->orWhereNotNull('pincode')->where('pincode', '!=', '');
                            })
                            ->latest('id')
                            ->first();
                    }

                    // Check latest order with address for this customer (prefer current business)
                    $order = DB::table('orders')
                        ->where('customer_id', $bc->customer_id)
                        ->where('business_id', $bc->business_id)
                        ->where(function ($q) {
                            $q->whereNotNull('address')->where('address', '!=', '')
                              ->orWhereNotNull('city')->where('city', '!=', '')
                              ->orWhereNotNull('state')->where('state', '!=', '')
                              ->orWhereNotNull('pincode')->where('pincode', '!=', '');
                        })
                        ->latest('id')
                        ->first();

                    if (!$order) {
                        $order = DB::table('orders')
                            ->where('customer_id', $bc->customer_id)
                            ->where(function ($q) {
                                $q->whereNotNull('address')->where('address', '!=', '')
                                  ->orWhereNotNull('city')->where('city', '!=', '')
                                  ->orWhereNotNull('state')->where('state', '!=', '')
                                  ->orWhereNotNull('pincode')->where('pincode', '!=', '');
                            })
                            ->latest('id')
                            ->first();
                    }

                    // Determine most recent between quotation and order
                    $source = null;
                    $fallback = null;
                    if ($quotation && $order) {
                        $quotationTime = strtotime($quotation->created_at ?? '') ?: 0;
                        $orderTime = strtotime($order->created_at ?? '') ?: 0;
                        if ($quotationTime >= $orderTime) {
                            $source = $quotation;
                            $fallback = $order;
                        } else {
                            $source = $order;
                            $fallback = $quotation;
                        }
                    } elseif ($quotation) {
                        $source = $quotation;
                    } elseif ($order) {
                        $source = $order;
                    }

                    $customerName = null;
                    $contact = null;
                    if ($user) {
                        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                        $customerName = $fullName !== '' ? $fullName : null;
                        $contact = !empty($user->contact) ? trim($user->contact) : null;
                    }

                    if (empty($customerName)) {
                        $customerName = !empty($source?->customer_name) ? trim($source->customer_name) : (!empty($fallback?->customer_name) ? trim($fallback->customer_name) : null);
                    }

                    if (empty($contact)) {
                        $contact = !empty($source?->customer_contact) ? trim($source->customer_contact) : (!empty($fallback?->customer_contact) ? trim($fallback->customer_contact) : null);
                    }

                    $address = !empty($source?->address) ? trim($source->address) : (!empty($fallback?->address) ? trim($fallback->address) : null);
                    $city = !empty($source?->city) ? trim($source->city) : (!empty($fallback?->city) ? trim($fallback->city) : null);
                    $state = !empty($source?->state) ? trim($source->state) : (!empty($fallback?->state) ? trim($fallback->state) : null);
                    $pincode = !empty($source?->pincode) ? trim($source->pincode) : (!empty($fallback?->pincode) ? trim($fallback->pincode) : null);

                    $businessName = !empty($bizName) ? trim($bizName) : null;

                    DB::table('business_customers')
                        ->where('id', $bc->id)
                        ->update([
                            'business_name' => $businessName,
                            'customer_name' => $customerName,
                            'contact' => $contact,
                            'address' => $address,
                            'state' => $state,
                            'city' => $city,
                            'pincode' => $pincode,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_customers', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'contact']);
            $table->dropColumn([
                'business_name',
                'customer_name',
                'contact',
                'address',
                'state',
                'city',
                'pincode',
            ]);

            $table->dropForeign(['customer_id']);
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
