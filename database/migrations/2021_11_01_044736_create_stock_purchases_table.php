<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_purchases', function (Blueprint $table) {
            $table->bigIncrements('stockpurchase_id');
            $table->string('stockpurchase_sys_id', 255)->unique();
            $table->string('stockpurchase_business_id', 255);
            $table->decimal('stockpurchase_price_per_stock_usd', 12, 2);
            $table->decimal('stockpurchase_total_price_no_fees_usd', 12, 2);
            $table->decimal('stockpurchase_risk_insurance_fee_usd', 12, 2);
            $table->decimal('stockpurchase_processing_fee_usd', 12, 2);
            $table->decimal('stockpurchase_total_price_with_all_fees_usd', 12, 2);
            $table->decimal('stockpurchase_rate_of_dollar_to_currency_paid_in', 12, 2);
            $table->decimal('stockpurchase_total_all_fees_in_currency_paid_in', 12, 2);
            $table->boolean('stockpurchase_processed')->default(false);
            $table->string('stockpurchase_processed_reason', 255);
            $table->boolean('stockpurchase_flagged')->default(false);
            $table->string('stockpurchase_flagged_reason', 255)->default("");
            $table->string('stockpurchase_payment_gateway_status', 255);
            $table->text('stockpurchase_payment_gateway_info');
            $table->timestamps();
        });

        Schema::table('stock_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('stockpurchase_currency_paid_in_id');
            $table->foreign('stockpurchase_currency_paid_in_id')->references('currency_id')->on('currencies');

            $table->unsignedBigInteger('stockpurchase_risk_insurance_type_id');
            $table->foreign('stockpurchase_risk_insurance_type_id')->references('risk_type_id')->on('risk_insurance_types');

            $table->string('stockpurchase_user_investor_id', 255);
            $table->foreign('stockpurchase_user_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_purchases');
    }
}
