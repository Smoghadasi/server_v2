<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreightInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freight_inquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('from_city_id');
            $table->unsignedInteger('to_city_id');
            $table->unsignedInteger('fleet_id');
            $table->integer('price')->default(0);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('freight_inquiries');
    }
}
