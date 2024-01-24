<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_owners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('state_id');
            $table->double('latitude');
            $table->double('longitude');
            $table->boolean('centerOfProvince')->default(false);
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city_owners');
    }
}
