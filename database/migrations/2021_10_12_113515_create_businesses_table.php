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
            $table->bigIncrements('drill_id');
            $table->string('drill_sys_id', 255)->unique();
            $table->string('drill_question', 255)->default;
            $table->string('drill_answer_1', 255);
            $table->string('drill_answer_2', 255);
            $table->string('drill_answer_3', 255)->default("");
            $table->string('drill_answer_4', 255)->default("");
            $table->text('drill_answer_implied_traits_1');
            $table->text('drill_answer_implied_traits_2');
            $table->text('drill_answer_implied_traits_3');
            $table->text('drill_answer_implied_traits_4');
            $table->boolean('drill_passed_as_suggestion')->default(false);
            $table->boolean('drill_flagged')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
}
