<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTrainDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_train_data', function (Blueprint $table) {
            $table->bigIncrements('stocktraindata_id');
            $table->string('stocktraindata_value_per_stock_usd_seven_inputs', 255);
            $table->string('stocktraindata_value_change_seven_inputs', 255);
            $table->string('stocktraindata_volume_seven_inputs', 255);
            $table->integer('stocktraindata_expected_output_o');
            $table->integer('stocktraindata_expected_output_c');
            $table->integer('stocktraindata_expected_output_e');
            $table->integer('stocktraindata_expected_output_a');
            $table->integer('stocktraindata_expected_output_n');
            $table->timestamps();
        });

        Schema::table('stock_train_data', function (Blueprint $table) {
            $table->string('stocktraindata_admin_adder_id', 255);
            $table->foreign('stocktraindata_admin_adder_id')->references('administrator_sys_id')->on('administrators');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_train_data');
    }
}
