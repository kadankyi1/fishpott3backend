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
            $table->string('investor_id', 255)->unique();
            $table->string('user_surname', 255)->default("");
            $table->string('user_firstname', 255);
            $table->string('user_pottname', 255)->unique();
            $table->date('user_dob');
            $table->string('user_phone_number', 255)->unique();
            $table->string('user_email', 255)->unique();
            $table->string('user_profile_picture', 255)->nullable();
            $table->string('password', 255);
            $table->bigInteger('user_net_worth_usd')->default(0);
            $table->bigInteger('user_pott_intelligence')->default(0);
            $table->bigInteger('user_pott_position')->default(0);
            $table->decimal('user_wallet_usd', 12, 2)->default(0);
            $table->integer('user_verified_tag')->default(0);
            $table->datetime('user_shield_date', $precision = 0);
            $table->string('user_referred_by', 255)->default("");
            $table->string('user_pott_ruler', 255)->default("");
            $table->string('user_fcm_token_android', 255)->default("");
            $table->string('user_fcm_token_web', 255)->default("");
            $table->string('user_fcm_token_ios', 255)->default("");
            $table->boolean('user_added_to_sitemap')->default(false);
            $table->boolean('user_reviewed_by_admin')->default(false);
            $table->decimal('ocean_openness_to_experience', 12, 2)->default(0.00);
            $table->decimal('ocean_conscientiousness', 12, 2)->default(0.00);
            $table->decimal('ocean_extraversion', 12, 2)->default(0.00);
            $table->decimal('ocean_agreeableness', 12, 2)->default(0.00);
            $table->decimal('ocean_neuroticism', 12, 2)->default(0.00);
            $table->text('user_scope');
            $table->string('user_android_app_version_code', 255)->default("");
            $table->string('user_ios_app_version_code', 255)->default("");
            $table->boolean('user_phone_verification_status')->default(false);
            $table->datetime('user_phone_verifcation_date')->nullable();
            $table->boolean('user_phone_verification_requested')->default(false);
            $table->boolean('user_id_verified_status')->default(false);
            $table->datetime('user_id_verifcation_date')->nullable();
            $table->boolean('user_id_verification_requested')->default(false);
            $table->datetime('user_last_sms_sent_datetime')->nullable();
            $table->boolean('user_can_post_media')->default(false);
            $table->boolean('user_initial_signup_approved')->default(false);
            $table->boolean('user_email_alerts_subscribed')->default(true);
            $table->boolean('user_flagged')->default(false);
            $table->text('user_flagged_reason')->nullable();
            $table->datetime('last_online')->useCurrent();
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_user_type_id');
            $table->foreign('user_user_type_id')->references('user_type_id')->on('user_types');
        
            $table->unsignedBigInteger('user_gender_id')->default(1);
            $table->foreign('user_gender_id')->references('gender_id')->on('genders');
        
            $table->unsignedBigInteger('user_language_id');
            $table->foreign('user_language_id')->references('language_id')->on('languages');
        
            $table->unsignedBigInteger('user_country_id');
            $table->foreign('user_country_id')->references('country_id')->on('countries');
        
            $table->unsignedBigInteger('user_currency_id');
            $table->foreign('user_currency_id')->references('currency_id')->on('currencies');
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
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
