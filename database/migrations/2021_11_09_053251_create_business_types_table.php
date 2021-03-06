<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->bigIncrements('businesstype_id');
            $table->string('businesstype_name', 255)->unique();
            $table->timestamps();
        });

        /*
        DB::table('genders')->insert([
            ['gender_id' => 1, 'businesstype_name' => 'Male'],
            ['gender_id' => 2, 'gender_name' => 'Female'],
            ['gender_id' => 3, 'gender_name' => 'Business']
        ]);
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('business_types');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
