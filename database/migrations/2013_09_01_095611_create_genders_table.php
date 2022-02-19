<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genders', function (Blueprint $table) {
            $table->bigIncrements('gender_id');
            $table->string('gender_name', 255)->unique();
            $table->timestamps();
        });

        DB::table('genders')->insert([
            ['gender_id' => 1, 'gender_name' => 'Male'],
            ['gender_id' => 2, 'gender_name' => 'Female'],
            ['gender_id' => 3, 'gender_name' => 'Business'],
            ['gender_id' => 4, 'gender_name' => 'Not Stated']
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
        Schema::dropIfExists('genders');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
