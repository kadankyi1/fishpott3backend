<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_values', function (Blueprint $table) {
            $table->bigIncrements('stockvalue_id');
            $table->decimal('stockvalue_value_per_stock_usd', 12, 2);
            $table->decimal('stockvalue_value_change', 12, 2);
            $table->integer('stockvalue_volume');
            $table->timestamps();
        });

        Schema::table('stock_values', function (Blueprint $table) {
            $table->string('stockvalue_admin_adder_id', 255);
            $table->foreign('stockvalue_admin_adder_id')->references('administrator_sys_id')->on('administrators');

            $table->string('stockvalue_business_id', 255);
            $table->foreign('stockvalue_business_id')->references('business_sys_id')->on('businesses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_values');
    }
}
