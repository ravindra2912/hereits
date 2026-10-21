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
            $table->unsignedBigInteger('business_id')->index();
            $table->boolean('is_appointment_system')->default(false);
            $table->boolean('is_appointment_with_department')->default(false)->comment('TRUE = department is mandatory, FALSE = department Not mandatory');
            $table->boolean('is_appointment_price_required')->default(false)->comment('TRUE = price is mandatory, FALSE = price Not mandatory');
            $table->boolean('is_appointment_creadit_diduct_manual')->default(false)->comment('TRUE = deduct credit using manual amounts, FALSE = deduct credit category wise');
            $table->double('deduct_credit_per_customer_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by customer');
            $table->double('deduct_credit_per_self_appointment', 10, 2)->default(1)->comment('Credits deducted when appointment is booked by business/self');
            $table->boolean('allow_appointment_product_selection')->default(false);

            $table->boolean('is_ecommerce_system')->default(false);
            $table->boolean('is_product_import_export')->default(false);
            $table->boolean('is_share_products_to_business')->default(false);
            $table->boolean('is_update_stock_and_price_using_exel')->default(false);
            $table->boolean('is_order_place_on_website')->default(true);
            $table->boolean('is_label')->default(false);

            $table->boolean('show_delivery_charges')->default(true);
            $table->decimal('same_state_shipping_charge_per_kg', 8, 2)->default(0.00);
            $table->decimal('other_state_shipping_charge_per_kg', 8, 2)->default(0.00);
            $table->boolean('accept_online_payments')->default(true);
            $table->boolean('accept_cash_on_delivery')->default(true);

            $table->boolean('is_offer_with_customer_group')->default(false);
            $table->boolean('is_lucky_draw')->default(false);

            $table->boolean('is_quotation_charge_manual')->default(false);
            $table->boolean('is_create_quotation_on_inquiry')->default(false);
            $table->double('charge_per_quotation', 10, 2)->default(1.00);

            $table->boolean('is_order_charge_manual')->default(false);
            $table->double('charge_place_order_on_website', 10, 2)->default(1.00);
            $table->double('charge_place_order_on_pos', 10, 2)->default(1.00);

            $table->boolean('is_draw_charge_manual')->default(false);
            $table->double('draw_credit_per_scratch', 10, 2)->default(1.00);

            $table->boolean('is_show_contact')->default(true);
            $table->boolean('is_private')->default(true);
            $table->boolean('is_login_required')->default(false);
            $table->json('theme_setting')->nullable();

            $table->boolean('is_service_system')->default(false);
            $table->boolean('is_pos_access')->default(false);
            $table->double('credit', 10, 2)->default(0);
            $table->boolean('is_verified')->default(false);
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
