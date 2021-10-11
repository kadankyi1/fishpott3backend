<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestos', function (Blueprint $table) {
            $table->bigIncrements('suggesto_id');
            $table->string('suggesto_sys_id', 255)->unique();
            $table->string('suggesto_question', 255)->default;
            $table->string('suggesto_answer_1', 255);
            $table->string('suggesto_answer_2', 255);
            $table->string('suggesto_answer_3', 255)->default("");
            $table->string('suggesto_answer_4', 255)->default("");
            $table->text('suggesto_answer_implied_traits_1');
            $table->text('suggesto_answer_implied_traits_2');
            $table->text('suggesto_answer_implied_traits_3');
            $table->text('suggesto_answer_implied_traits_4');
            $table->boolean('suggesto_broadcasted')->default(false);
            $table->boolean('suggesto_flagged')->default(false);
            $table->timestamps();
        });

        Schema::table('suggestos', function (Blueprint $table) {
            $table->string('suggesto_maker_investor_id', 255);
            $table->foreign('suggesto_maker_investor_id')->references('investor_id')->on('users');
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
        Schema::dropIfExists('suggestos');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
