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
        if (!Schema::hasTable('business_payment_configurations')) {
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
        }

        // 2. Migrate existing online_payment_method & upi_id data from business_settings if legacy columns exist
        if (Schema::hasTable('business_settings') && Schema::hasColumn('business_settings', 'online_payment_method')) {
            $selectCols = ['business_id', 'online_payment_method'];
            if (Schema::hasColumn('business_settings', 'upi_id')) {
                $selectCols[] = 'upi_id';
            }

            $settings = DB::table('business_settings')->select($selectCols)->get();
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
        Schema::dropIfExists('business_payment_configurations');
    }
};
