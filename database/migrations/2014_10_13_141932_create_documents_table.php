<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('document_id');
            $table->string('document_number', 255);
            $table->text('document_added_notes');
            $table->string('document_url', 255)->unique();
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('document_owner_user_id');
            $table->foreign('document_owner_user_id')->references('user_id')->on('users');
        
            $table->unsignedBigInteger('document_type_id');
            $table->foreign('document_type_id')->references('document_type_id')->on('document_types');
        
            $table->unsignedBigInteger('document_origin_country_id');
            $table->foreign('document_origin_country_id')->references('country_id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
