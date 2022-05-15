<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks_transfers', function (Blueprint $table) {
            $table->bigIncrements('stocktransfer_id');
            $table->string('stocktransfer_sys_id', 255)->unique();
            $table->integer('stocktransfer_stocks_quantity');
            $table->string('stocktransfer_receiver_pottname', 255);
            $table->decimal('stocktransfer_total_cost_usd_value_of_shares_transfer', 12, 2);
            $table->boolean('stocktransfer_flagged')->default(false);
            $table->string('stocktransfer_flagged_reason', 255)->default("");
            $table->integer('stockstransfers_processed')->default(0);
            $table->string('stockstransfers_processed_reason', 255);
            $table->decimal('stocktransfer_rate_cedi_to_usd', 12, 2);
            $table->decimal('stocktransfer_processing_fee_usd', 12, 2);
            $table->decimal('stocktransfer_processing_local_currency_paid_in_amt', 12, 2);
            $table->integer('stocktransfer_payment_gateway_status')->default(0);
            $table->string('stocktransfer_payment_gateway_info', 255)->default("");
            $table->timestamps();
        });

        Schema::table('stocks_transfers', function (Blueprint $table) {
            $table->string('stocktransfer_business_id', 255);
            $table->foreign('stocktransfer_business_id')->references('business_sys_id')->on('businesses');

            $table->unsignedBigInteger('stocktransfer_processingfee_curr_paid_in_id');
            $table->foreign('stocktransfer_processingfee_curr_paid_in_id')->references('currency_id')->on('currencies');

            $table->string('stocktransfer_sender_investor_id', 255);
            $table->foreign('stocktransfer_sender_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks_transfers');
    }
}
