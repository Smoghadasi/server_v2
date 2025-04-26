<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractCollaborationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_collaborations', function (Blueprint $table) {
            $table->id();
            $table->integer('contractNumber')->nullable();
            $table->timestamp('fromDate')->nullable();
            $table->timestamp('toDate')->nullable();
            $table->boolean('isInsurance')->default(0);
            $table->string('contractType')->nullable();
            $table->string('contract_file')->nullable();
            $table->unsignedInteger('contract_id');

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
        Schema::dropIfExists('contract_collaborations');
    }
}
