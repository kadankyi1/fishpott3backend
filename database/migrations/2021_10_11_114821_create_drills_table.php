<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drills', function (Blueprint $table) {
            $table->bigIncrements('question_id');
            $table->string('question_sys_id', 255)->unique();
            $table->string('question_question', 255)->default;
            $table->string('question_answer_1', 255);
            $table->string('question_answer_2', 255);
            $table->string('question_answer_3', 255)->default("");
            $table->string('question_answer_4', 255)->default("");
            $table->text('question_answer_implied_traits_1');
            $table->text('question_answer_implied_traits_2');
            $table->text('question_answer_implied_traits_3');
            $table->text('question_answer_implied_traits_4');
            $table->boolean('question_passed_as_suggesto')->default(false);
            $table->boolean('question_flagged')->default(false);
            $table->timestamps();
        });

        Schema::table('drills', function (Blueprint $table) {
            $table->string('question_maker_investor_id', 255);
            $table->foreign('question_maker_investor_id')->references('investor_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drills');
    }
}
