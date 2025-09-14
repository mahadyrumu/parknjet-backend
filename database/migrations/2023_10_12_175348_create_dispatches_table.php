<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->integer('cid')->unsigned()->nullable();
            $table->tinyInteger('lot_id')->unsigned();
            $table->integer('rsvn')->unsigned()->nullable();
            $table->string('type')->length(5)->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('e0')->nullable();
            $table->dateTime('e1')->nullable();
            $table->dateTime('e2')->nullable();
            $table->string('comment')->length(512)->nullable();
            $table->smallInteger('delay')->nullable();
            $table->tinyInteger('active')->length(1)->nullable();
            $table->tinyInteger('island')->nullable();
            $table->string('phone')->length(15)->nullable();
            $table->string('e0_ipa')->length(15)->nullable();
            $table->string('e1_ipa')->length(15)->nullable();
            $table->string('e2_ipa')->length(15)->nullable();
            $table->binary('x')->length(1)->nullable();
            $table->tinyInteger('xflags')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatch');
    }
};
