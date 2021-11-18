<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiStockPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_stock_personas', function (Blueprint $table) {
            $table->bigIncrements('aistockpersona_id');
            $table->decimal('aistockpersona_openness_to_experience', 12, 2);
            $table->decimal('aistockpersona_conscientiousness', 12, 2);
            $table->decimal('aistockpersona_extraversion', 12, 2);
            $table->decimal('aistockpersona_agreeableness', 12, 2);
            $table->decimal('aistockpersona_neuroticism', 12, 2);
            $table->timestamps();
        });

        Schema::table('ai_stock_personas', function (Blueprint $table) {
            $table->string('aistockpersona_stock_business_id', 255);
            $table->foreign('aistockpersona_stock_business_id')->references('business_sys_id')->on('businesses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_stock_personas');
    }
}
