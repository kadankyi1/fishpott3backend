<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOwnershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_ownerships', function (Blueprint $table) {
            $table->bigIncrements('stockownership_id');
            $table->string('stockownership_sys_id', 255)->unique();
            $table->integer('stockownership_stocks_quantity')->default(0);
            $table->decimal('stockownership_total_cost_usd', 12, 2);
            $table->boolean('stockownership_flagged')->default(false);
            $table->string('stockownership_flagged_reason', 255)->default("");
            $table->timestamps();
        });

        Schema::table('stock_ownerships', function (Blueprint $table) {
            $table->unsignedBigInteger('stockpurchase_business_id');
            $table->foreign('stockpurchase_business_id')->references('business_sys_id')->on('businesses');

            $table->string('stockownership_user_investor_id', 255);
            $table->foreign('stockownership_user_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_ownerships');
    }
}
