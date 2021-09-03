<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('language_id');
            $table->string('language_full_name', 255)->unique();
            $table->string('language_short_name', 255)->unique();
            $table->timestamps();
        });


        DB::table('languages')->insert([
            ['language_id' => 1, 'language_full_name' => 'English', 'language_short_name' => 'en']
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
        Schema::dropIfExists('languages');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
