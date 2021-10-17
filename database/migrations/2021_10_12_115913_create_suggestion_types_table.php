<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestion_types', function (Blueprint $table) {
            $table->bigIncrements('suggestion_type_id');
            $table->string('suggestion_type_name', 255)->unique();
            $table->timestamps();
        });

        DB::table('suggestion_types')->insert([
            ['suggestion_type_id' => 1, 'suggestion_type_name' => 'Drill'],
            ['suggestion_type_id' => 2, 'suggestion_type_name' => 'Business']
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
        Schema::dropIfExists('suggestion_types');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
