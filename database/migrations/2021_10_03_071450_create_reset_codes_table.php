<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResetCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reset_codes', function (Blueprint $table) {
            $table->bigIncrements('resetcode_id');
            $table->string('resetcode', 255);
            $table->boolean('resetcode_used_status')->default(false);
            $table->timestamps();
        });

        Schema::table('reset_codes', function (Blueprint $table) {
            $table->string('user_investor_id', 255);
            $table->foreign('user_investor_id')->references('investor_id')->on('users');
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
        Schema::dropIfExists('reset_codes');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
