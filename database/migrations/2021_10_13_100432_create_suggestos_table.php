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
            $table->string('suggesto_item_reference_id', 255);
            $table->boolean('suggesto_broadcasted')->default(false);
            $table->boolean('suggesto_flagged')->default(false);
            $table->timestamps();
        });

        Schema::table('suggestos', function (Blueprint $table) {
            $table->unsignedBigInteger('suggesto_suggesto_type_id');
            $table->foreign('suggesto_suggesto_type_id')->references('suggesto_type_id')->on('suggesto_types');
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
