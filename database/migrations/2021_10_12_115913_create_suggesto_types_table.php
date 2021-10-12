<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestoTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggesto_types', function (Blueprint $table) {
            $table->bigIncrements('suggesto_type_id');
            $table->string('suggesto_type_name', 255)->unique();
            $table->timestamps();
        });

        DB::table('suggesto_types')->insert([
            ['suggesto_type_id' => 1, 'suggesto_type_name' => 'Question'],
            ['suggesto_type_id' => 2, 'suggesto_type_name' => 'Stock']
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('suggesto_types');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
