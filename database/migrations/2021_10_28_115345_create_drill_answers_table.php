<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDrillAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drill_answers', function (Blueprint $table) {
            $table->bigIncrements('drill_answer_id');
            $table->string('drill_answer_sys_id', 255)->unique();
            $table->integer('drill_answer_number');
            $table->boolean('drill_answer_used_for_pott_intelligence_calculation')->default(false);
            $table->timestamps();
        });

        Schema::table('drill_answers', function (Blueprint $table) {
            $table->string('drill_answer_drill_sys_id', 255);
            $table->foreign('drill_answer_drill_sys_id')->references('drill_sys_id')->on('drills');

            $table->string('drill_answer_user_investor_id', 255);
            $table->foreign('drill_answer_user_investor_id')->references('investor_id')->on('users');
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
        Schema::dropIfExists('drill_answers');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
