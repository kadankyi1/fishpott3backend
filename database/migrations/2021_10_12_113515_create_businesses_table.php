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
            $table->string('business_pottname', 255)->default("");
            $table->boolean('business_flagged')->default(false);
            $table->text('business_flagged_reason');
            $table->timestamps();
            $table->string('business_registration_number', 255)->unique();
            $table->string('business_type', 255);
            $table->string('business_logo', 255)->unique();
            $table->string('business_full_name', 255);
            $table->string('business_stockmarket_shortname', 255)->default("");
            $table->string('business_descriptive_bio', 255);
            $table->string('business_address', 255);
            $table->date('business_start_date');
            $table->string('business_website', 255);
            $table->string('business_pitch_text', 255);
            $table->string('business_pitch_video', 255);
            $table->bigInteger('business_lastyr_revenue_usd');
            $table->bigInteger('business_lastyr_profit_or_loss_usd');
            $table->bigInteger('business_debt_usd');
            $table->bigInteger('business_cash_on_hand_usd');
            $table->bigInteger('business_net_worth_usd');
            $table->bigInteger('business_investments_amount_needed_usd');
            $table->bigInteger('business_investments_amount_received_usd');
            $table->integer('business_maximum_number_of_investors_allowed');
            $table->integer('business_current_shareholders');
            $table->string('business_full_financial_report_pdf_url', 255);
            $table->text('business_descriptive_financial_bio');
            $table->string('business_executive1_firstname', 255);
            $table->string('business_executive1_lastname', 255);
            $table->string('business_executive1_profile_picture', 255)->default("");
            $table->string('business_executive1_position', 255);
            $table->text('business_executive1_description');
            $table->string('business_executive1_facebook_url', 255)->default("");
            $table->string('business_executive1_linkedin_url', 255)->default("");
            $table->string('business_executive2_firstname', 255);
            $table->string('business_executive2_lastname', 255);
            $table->string('business_executive2_profile_picture', 255)->default("");
            $table->string('business_executive2_position', 255);
            $table->text('business_executive2_description');
            $table->string('business_executive2_facebook_url', 255)->default("");
            $table->string('business_executive2_linkedin_url', 255)->default("");
            $table->string('business_executive3_firstname', 255)->default("");
            $table->string('business_executive3_lastname', 255)->default("");
            $table->string('business_executive3_profile_picture', 255)->default("");
            $table->string('business_executive3_position', 255)->default("");
            $table->text('business_executive3_description');
            $table->string('business_executive3_facebook_url', 255)->default("");
            $table->string('business_executive3_linkedin_url', 255)->default("");
            $table->string('business_executive4_firstname', 255)->default("");
            $table->string('business_executive4_lastname', 255)->default("");
            $table->string('business_executive4_profile_picture', 255)->default("");
            $table->string('business_executive4_position', 255)->default("");
            $table->text('business_executive4_description');
            $table->string('business_executive4_facebook_url', 255)->default("");
            $table->string('business_executive4_linkedin_url', 255)->default("");
        });

        
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedBigInteger('business_country_id');
            $table->foreign('business_country_id')->references('country_id')->on('countries');
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
