<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('block_phone_numbers', function (Blueprint $table) {
            $table->enum('type', ['operator', 'owner', 'both'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('block_phone_numbers', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
