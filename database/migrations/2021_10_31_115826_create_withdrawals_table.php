<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->bigIncrements('withdrawal_id');
            $table->string('withdrawal_sys_id', 255)->unique();
            $table->decimal('withdrawal_amt_usd', 12, 2);
            $table->decimal('withdrawal_amt_local', 12, 2);
            $table->string('withdrawal_local_currency_sign', 255);
            $table->decimal('withdrawal_rate', 12, 2);
            $table->string('withdrawal_receiving_bank_or_momo_account_name', 255);
            $table->string('withdrawal_receiving_bank_or_momo_account_number', 255);
            $table->string('withdrawal_receiving_bank_or_momo_name', 255);
            $table->string('withdrawal_receiving_bank_routing_number', 255)->default("");
            $table->integer('withdrawal_paid')->default(0);
            $table->boolean('withdrawal_flagged')->default(false);
            $table->string('withdrawal_flagged_reason', 255)->default("");
            $table->timestamps();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('withdrawal_user_investor_id', 255);
            $table->foreign('withdrawal_user_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('withdrawals');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
