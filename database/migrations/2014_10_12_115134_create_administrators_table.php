<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->bigIncrements('administrator_id');
            $table->string('administrator_surname', 255);
            $table->string('administrator_firstname', 255);
            $table->string('administrator_phone_number', 255)->unique();
            $table->string('administrator_email', 255)->unique();
            $table->string('administrator_pin', 255);
            $table->string('password', 255);
            $table->text('administrator_scope');
            $table->boolean('administrator_flagged');
            $table->unsignedBigInteger('creator_administrator_id');
            $table->timestamps();
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->string('administrator_user_pottname', 255);
            $table->foreign('administrator_user_pottname')->references('user_pottname')->on('users');
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
        Schema::dropIfExists('administrators');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
