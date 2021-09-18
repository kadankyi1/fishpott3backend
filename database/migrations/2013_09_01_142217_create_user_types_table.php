<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->bigIncrements('user_type_id');
            $table->string('user_type_fullname', 255);
            $table->string('user_type_shortname', 255);
            $table->timestamps();
        });

        DB::table('user_types')->insert([
            ['user_type_id' => 1, 'user_type_fullname' => 'Invester', 'user_type_shortname' => 'I'],
            ['user_type_id' => 2, 'user_type_fullname' => 'Business', 'user_type_shortname' => 'B'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_types');
    }
}
