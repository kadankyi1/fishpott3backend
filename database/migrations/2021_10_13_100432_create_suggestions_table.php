<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->bigIncrements('suggestion_id');
            $table->string('suggestion_sys_id', 255)->unique();
            $table->string('suggestion_item_reference_id', 255);
            $table->string('suggestion_directed_at_user_investor_id', 255);
            $table->string('suggestion_directed_at_user_business_find_code', 255);
            $table->string('suggestion_reason', 255)->default('');
            $table->boolean('suggestion_notification_sent')->default(false);
            $table->boolean('suggestion_passed_on_by_user')->default(false);
            $table->boolean('suggestion_flagged')->default(false);
            $table->timestamps();
        });

        Schema::table('suggestions', function (Blueprint $table) {
            $table->unsignedBigInteger('suggestion_suggestion_type_id');
            $table->foreign('suggestion_suggestion_type_id')->references('suggestion_type_id')->on('suggestion_types');
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
        Schema::dropIfExists('suggestions');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
