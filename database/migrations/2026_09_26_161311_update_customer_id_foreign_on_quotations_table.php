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
        // 1. Drop foreign key referencing users table
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        // 2. Map existing quotations.customer_id (user_id) to business_customers.id
        $quotations = DB::table('quotations')->whereNotNull('customer_id')->get();
        foreach ($quotations as $q) {
            $bc = DB::table('business_customers')
                ->where('business_id', $q->business_id)
                ->where('customer_id', $q->customer_id)
                ->first();

            if (!$bc && $q->customer_contact) {
                $clean = preg_replace('/[^0-9]/', '', $q->customer_contact) ?: $q->customer_contact;
                $bc = DB::table('business_customers')
                    ->where('business_id', $q->business_id)
                    ->where(function ($query) use ($q, $clean) {
                        $query->where('contact', $q->customer_contact)
                              ->orWhere('contact', $clean);
                    })
                    ->first();
            }

            if (!$bc) {
                $user = DB::table('users')->where('id', $q->customer_id)->first();
                $bcId = DB::table('business_customers')->insertGetId([
                    'business_id' => $q->business_id,
                    'customer_id' => $q->customer_id,
                    'customer_name' => $q->customer_name ?: ($user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : 'Customer'),
                    'contact' => $q->customer_contact ?: ($user?->contact ?: null),
                    'address' => $q->address,
                    'city' => $q->city,
                    'state' => $q->state,
                    'pincode' => $q->pincode,
                    'created_at' => $q->created_at ?: now(),
                    'updated_at' => $q->updated_at ?: now(),
                ]);
                DB::table('quotations')->where('id', $q->id)->update(['customer_id' => $bcId]);
            } else {
                DB::table('quotations')->where('id', $q->id)->update(['customer_id' => $bc->id]);
            }
        }

        // 3. Rename column from customer_id to business_customer_id
        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'business_customer_id');
        });

        // 4. Add foreign key referencing business_customers table
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('business_customer_id')->references('id')->on('business_customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign key referencing business_customers table
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['business_customer_id']);
        });

        // 2. Rename column back to customer_id
        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('business_customer_id', 'customer_id');
        });

        // 3. Map quotations.customer_id back to users.id from business_customers table
        $quotations = DB::table('quotations')->whereNotNull('customer_id')->get();
        foreach ($quotations as $q) {
            $userId = DB::table('business_customers')->where('id', $q->customer_id)->value('customer_id');
            DB::table('quotations')->where('id', $q->id)->update(['customer_id' => $userId]);
        }

        // 4. Re-add foreign key referencing users table
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users');
        });
    }
};
