<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->bigIncrements('currency_id');
            $table->string('currency_full_name', 255)->unique();
            $table->string('currency_short_name', 255)->unique();
            $table->string('currency_symbol', 255)->unique();
            $table->timestamps();
        });

        DB::table('currencies')->insert([
            [
                'currency_id' => 1, 
                'currency_full_name' => 'United States Dollars', 
                'currency_short_name' => 'USD', 
                'currency_symbol' => '$'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('currencies');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
