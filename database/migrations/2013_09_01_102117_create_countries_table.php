<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('country_id');
            $table->string('country_iso_2char_name', 255)->unique();
            $table->string('country_real_name', 255)->unique();
            $table->string('country_nice_name', 255)->unique();
            $table->string('country_iso_3char_name', 255)->unique();
            $table->string('country_name_num_code', 255)->unique();
            $table->string('country_phone_num_code', 255)->unique();
            $table->boolean('country_can_get_offers')->default(false);;
            $table->boolean('country_can_trade')->default(false);;
            $table->timestamps();
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
        Schema::dropIfExists('countries');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
