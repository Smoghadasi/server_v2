<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityDistanceCalculatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_distance_calculates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fromCity_id');
            $table->unsignedInteger('toCity_id');
            $table->string('value');

            $table->foreign('fromCity_id')->references('id')->on('city_owners')->onDelete('cascade');
            $table->foreign('toCity_id')->references('id')->on('city_owners')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city_distance_calculates');
    }
}
