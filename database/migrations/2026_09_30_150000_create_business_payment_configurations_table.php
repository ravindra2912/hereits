<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create business_payment_configurations table
        Schema::create('business_payment_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->unique();
            $table->enum('payment_method', ['manual_upi', 'razorpay'])->default('manual_upi');
            $table->string('upi_id')->nullable();
            $table->string('razorpay_key_id')->nullable();
            $table->text('razorpay_key_secret')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
        });

        // 2. Migrate existing online_payment_method & upi_id data from business_settings
        if (Schema::hasTable('business_settings')) {
            $settings = DB::table('business_settings')->select('business_id', 'online_payment_method', 'upi_id')->get();
            foreach ($settings as $setting) {
                if (!empty($setting->business_id)) {
                    $method = $setting->online_payment_method ?? 'manual_upi';
                    if (!in_array($method, ['manual_upi', 'razorpay'])) {
                        $method = 'manual_upi';
                    }
                    DB::table('business_payment_configurations')->updateOrInsert(
                        ['business_id' => $setting->business_id],
                        [
                            'payment_method' => $method,
                            'upi_id' => $setting->upi_id ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            // 3. Drop online_payment_method and upi_id from business_settings table
            Schema::table('business_settings', function (Blueprint $table) {
                if (Schema::hasColumn('business_settings', 'online_payment_method')) {
                    $table->dropColumn('online_payment_method');
                }
                if (Schema::hasColumn('business_settings', 'upi_id')) {
                    $table->dropColumn('upi_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add online_payment_method and upi_id to business_settings
        Schema::table('business_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('business_settings', 'online_payment_method')) {
                $table->string('online_payment_method')->default('manual_upi')->after('accept_online_payments');
            }
            if (!Schema::hasColumn('business_settings', 'upi_id')) {
                $table->string('upi_id')->nullable()->after('online_payment_method');
            }
        });

        // 2. Restore data from business_payment_configurations back to business_settings
        if (Schema::hasTable('business_payment_configurations')) {
            $configs = DB::table('business_payment_configurations')->get();
            foreach ($configs as $config) {
                DB::table('business_settings')
                    ->where('business_id', $config->business_id)
                    ->update([
                        'online_payment_method' => $config->payment_method,
                        'upi_id' => $config->upi_id,
                    ]);
            }
        }

        // 3. Drop business_payment_configurations table
        Schema::dropIfExists('business_payment_configurations');
    }
};
