<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverAwareNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_aware_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fromCity_id');
            $table->unsignedInteger('toCity_id');
            $table->unsignedInteger('fleet_id');
            $table->unsignedInteger('driver_id');
            $table->timestamp('date')->nullable();
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
        Schema::dropIfExists('driver_aware_notifications');
    }
}
