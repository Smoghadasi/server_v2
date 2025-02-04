<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trackable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobileNumber');
            $table->string('date')->nullable();
            $table->integer('tracking_code')->nullable();
            $table->unsignedInteger('parent_id')->default(0);
            $table->text('description')->nullable();
            $table->text('result')->nullable();
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
        Schema::dropIfExists('trackable_items');
    }
}
