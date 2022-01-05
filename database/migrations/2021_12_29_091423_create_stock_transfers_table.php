<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->bigIncrements('stocktransfer_id');
            $table->string('stocktransfer_sys_id', 255)->unique();
            $table->integer('stocktransfer_stocks_quantity');
            $table->string('stocktransfer_receiver_pottname', 255)->unique();
            $table->boolean('stocktransfer_flagged')->default(false);
            $table->string('stocktransfer_flagged_reason', 255)->default("");
            $table->integer('stocktransfer_payment_gateway_status')->default(0);
            $table->text('stocktransfer_payment_gateway_info');
            $table->timestamps();
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->string('stocktransfer_business_id', 255);
            $table->foreign('stocktransfer_business_id')->references('business_sys_id')->on('businesses');

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
        Schema::dropIfExists('stock_transfers');
    }
}
