<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->string('transaction_sys_id', 255)->unique();
            $table->string('transaction_referenced_item_id', 255);
            $table->timestamps();
        });


        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_transaction_type_id');
            $table->foreign('transaction_transaction_type_id')->references('transaction_type_id')->on('transaction_types');

            $table->string('transaction_user_investor_id', 255);
            $table->foreign('transaction_user_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
