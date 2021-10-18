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
            $table->bigIncrements('admin_id');
            $table->string('admin_surname', 255);
            $table->string('admin_firstname', 255);
            $table->string('admin_othernames', 255)->nullable();
            $table->string('admin_phone_number', 255)->unique();
            $table->string('admin_email', 255)->unique();
            $table->string('admin_pin', 255);
            $table->string('password', 255);
            $table->text('admin_scope');
            $table->boolean('admin_flagged');
            $table->unsignedBigInteger('creator_admin_id');
            $table->timestamps();
        });

        Schema::table('drills', function (Blueprint $table) {
            $table->string('drill_maker_investor_id', 255);
            $table->foreign('drill_maker_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('administrators');
    }
}
