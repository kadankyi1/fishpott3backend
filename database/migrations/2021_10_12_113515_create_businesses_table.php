<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->bigIncrements('business_id');
            $table->string('business_sys_id', 255)->unique();
            $table->string('business_pott_name', 255);
            $table->boolean('business_flagged')->default(false);
            $table->text('business_flagged_reason');
            $table->timestamps();
            $table->string('business_type', 255);
            $table->string('business_logo', 255)->unique();
            $table->string('business_full_name', 255);
            $table->string('business_short_name', 255);
            $table->string('business_descriptive_bio', 255);
            $table->string('business_address', 255);
            $table->string('business_pitch_text', 255);
            $table->string('business_pitch_video', 255);
            $table->bigInteger('business_revenue_usd');
            $table->bigInteger('business_loss_usd');
            $table->bigInteger('business_debt_usd');
            $table->bigInteger('business_cash_on_hand_usd');
            $table->bigInteger('business_net_worth_usd');
            $table->bigInteger('business_net_valuation_usd');
            $table->bigInteger('business_investments_amount_needed_usd');
            $table->integer('business_maximum_number_of_investors_allowed');
            $table->text('business_descriptive_financial_bio');
            $table->string('business_executive1_firstname', 255);
            $table->string('business_executive1_lastname', 255);
            $table->string('business_executive1_profile_picture', 255);
            $table->text('business_executive1_description');
            $table->string('business_executive1_facebook_url', 255);
            $table->string('business_executive1_linkedin_url', 255);
            $table->string('business_address', 255);
            $table->text('drill_answer_implied_traits_3');
            $table->text('drill_answer_implied_traits_4');
            $table->boolean('drill_passed_as_suggestion')->default(false);
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('businesses');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
