<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->boolean('is_appointment_system')->default(false);
            $table->boolean('is_appointment_with_department')->default(false)->comment('TRUE = department is mendetory, FALSE = department Not mandetory');
            $table->boolean('is_appointment_price_required')->default(false)->comment('TRUE = price is mendetory, FALSE = price Not mandetory');
            $table->boolean('is_appointment_creadit_diduct_manual')->default(false)->comment('TRUE = deduct creadit using deduct_credit_per_customer_appointment and deduct_credit_per_self_appointment, FALSE = deduct credit category wise');
            $table->double('deduct_credit_per_customer_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by customer');
            $table->double('deduct_credit_per_self_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by business/self');
            $table->boolean('is_ecommerce_system')->default(false);
            $table->boolean('is_product_import_export')->default(false);
            $table->boolean('is_service_system')->default(false);
            $table->boolean('is_pos_access')->default(false);
            $table->date('subscription_expiry_date')->nullable();
            $table->double('credit', 10, 2)->default(0);
            $table->bigInteger('product_limit')->default(0);
            $table->date('product_limit_expiry_date')->nullable();
            $table->bigInteger('service_limit')->default(0);
            $table->date('service_limit_expiry_date')->nullable();
            $table->boolean('is_verified')->default(false);
            // $table->boolean('is_google_drive_connected')->default(false);
            // $table->string('google_drive_folder_id')->nullable()->comment('Google Drive Folder ID');
            // $table->string('google_drive_client_id')->nullable()->comment('Google Drive Client ID');
            // $table->string('google_drive_client_secret')->nullable()->comment('Google Drive Client Secret');
            // $table->string('google_drive_refresh_token')->nullable()->comment('Google Drive Refresh Token');
            $table->text('about_us_text')->nullable()->comment('About Us');
            $table->string('about_us_image')->nullable()->comment('About Us Image');

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
