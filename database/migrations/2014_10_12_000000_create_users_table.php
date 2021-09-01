<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('user_surname', 255);
            $table->string('user_firstname', 255);
            $table->string('user_pottname', 255);
            $table->date('user_dob');
            $table->string('user_phone_number', 255)->unique();
            $table->string('user_email', 255)->nullable();
            $table->string('user_profile_picture', 255)->nullable();
            $table->string('password', 255);
            $table->string('user_country', 255);
            $table->string('user_language', 255);
            $table->string('user_currency', 255);
            $table->string('user_net_worth', 255);
            $table->boolval('user_verified_tag');
            $table->datetime('user_shield_date');
            $table->string('user_referred_by', 255);
            $table->string('user_pott_ruler', 255);
            $table->string('user_fcm_token_android', 255);
            $table->string('user_fcm_token_web', 255);
            $table->string('user_fcm_token_ios', 255);
            $table->string('user_added_to_sitemap', 255);
            $table->string('user_reviewed_by_admin', 255);
            $table->text('user_scope');
            $table->boolval('user_flagged');
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_gender_id');
            $table->foreign('user_gender_id')->references('gender_id')->on('genders');
        
            $table->unsignedBigInteger('user_language_id');
            $table->foreign('user_language_id')->references('language_id')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
