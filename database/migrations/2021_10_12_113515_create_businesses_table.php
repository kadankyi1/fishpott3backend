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
            $table->string('business_address', 255);
            $table->string('business_address', 255);
            $table->string('business_address', 255);
            $table->string('business_address', 255);
            $table->text('drill_answer_implied_traits_2');
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
