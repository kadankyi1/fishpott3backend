<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->bigIncrements('transaction_type_id');
            $table->string('transaction_type_fullname', 255);
            $table->string('transaction_type_shortname', 255);
            $table->timestamps();
        });

        DB::table('transaction_types')->insert([
            ['transaction_type_id' => 1, 'transaction_type_fullname' => 'Withdrawal', 'transaction_type_shortname' => 'WD'],
            ['transaction_type_id' => 2, 'transaction_type_fullname' => 'Credit', 'transaction_type_shortname' => 'CD'],
            ['transaction_type_id' => 3, 'transaction_type_fullname' => 'Dividend', 'transaction_type_shortname' => 'DI'],
            ['transaction_type_id' => 4, 'transaction_type_fullname' => 'Stock Purchase', 'transaction_type_shortname' => 'SP']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_types');
    }
}
