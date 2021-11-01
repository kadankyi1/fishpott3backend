<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskInsuranceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_insurance_types', function (Blueprint $table) {
            $table->bigIncrements('risk_type_id');
            $table->string('risk_type_fullname', 255);
            $table->string('risk_type_shortname', 255);
            $table->string('risk_type_description', 255);
            $table->timestamps();
        });

        DB::table('risk_insurance_types')->insert([
            [
                'risk_type_id' => 1, 
                'risk_type_fullname' => '100% Investment Risk Protection', 
                'risk_type_shortname' => '100% Risk Insurance', 
                'risk_type_description' => 'Choosing no risk insurance means if the business fails, FishPott will not pay any amount back to you to cushion you.'
            ],
            [
                'risk_type_id' => 2, 
                'risk_type_fullname' => '50% Investment Risk Protection', 
                'risk_type_shortname' => '50% Risk Insurance', 
                'risk_type_description' => 'Choosing 50% risk insurance means if the business fails, FishPott reimburse 50% what you paid for the shares.'
            ],
            [
                'risk_type_id' => 3, 
                'risk_type_fullname' => '0% Investment Risk Protection', 
                'risk_type_shortname' => 'No Risk Insurance', 
                'risk_type_description' => 'Choosing 100% risk insurance means if the business fails, FishPott reimburse 100% what you paid for the shares.'
            ]
            
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_insurance_types');
    }
}
