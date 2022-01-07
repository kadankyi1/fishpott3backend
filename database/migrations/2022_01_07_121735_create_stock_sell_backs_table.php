<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockSellBacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_sell_backs', function (Blueprint $table) {
            $table->bigIncrements('stocksellback_id');
            $table->string('stocksellback_sys_id', 255)->unique();
            $table->integer('stocksellback_stocks_quantity');
            $table->decimal('stocksellback_buyback_offer_per_stock_usd', 12, 2);
            $table->decimal('stocksellback_payout_amt_local_currency_paid_in', 12, 2);
            $table->string('stocksellback_receiving_bank_or_momo_account_name', 255)->default("");
            $table->string('stocksellback_receiving_bank_or_momo_account_number', 255)->default("");
            $table->string('stocksellback_receiving_bank_or_momo_name', 255)->default("");
            $table->string('stocksellback_receiving_bank_routing_number', 255)->default("");
            $table->decimal('stocksellback_rate_dollar_to_local_with_no_signs', 12, 2);
            $table->decimal('stocksellback_processing_fee_usd', 12, 2);
            $table->boolean('stocksellback_flagged')->default(false);
            $table->string('stocksellback_flagged_reason', 255)->default("");
            $table->integer('stocksellback_processed')->default(0);
            $table->string('stocksellback_processed_reason', 255)->default("");
            $table->timestamps();
        });

        Schema::table('stock_sell_backs', function (Blueprint $table) {
            $table->string('stocksellback_business_id', 255);
            $table->foreign('stocksellback_business_id')->references('business_sys_id')->on('businesses');

            $table->unsignedBigInteger('stocksellback_local_currency_paid_in_id');
            $table->foreign('stocksellback_local_currency_paid_in_id')->references('currency_id')->on('currencies');

            $table->string('stocksellback_seller_investor_id', 255);
            $table->foreign('stocksellback_seller_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_sell_backs');
    }
}
