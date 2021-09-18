<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->bigIncrements('document_type_id');
            $table->string('document_fullname', 255);
            $table->string('document_shortname', 255);
            $table->timestamps();
        });

        DB::table('document_types')->insert([
            ['document_type_id' => 1, 'document_fullname' => 'Driver License', 'document_shortname' => 'DL'],
            ['document_type_id' => 2, 'document_fullname' => 'National ID', 'document_shortname' => 'NI'],
            ['document_type_id' => 3, 'document_fullname' => 'State ID', 'document_shortname' => 'SI'],
            ['document_type_id' => 4, 'document_fullname' => 'Voter ID', 'document_shortname' => 'VID'],
            ['document_type_id' => 5, 'document_fullname' => 'Passport', 'document_shortname' => 'PP'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_types');
    }
}
