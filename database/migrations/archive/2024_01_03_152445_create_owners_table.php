<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('lastName');
            $table->string('mobileNumber');
            $table->string('nationalCode');
            $table->string('ip')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('companyName')->nullable(); // نام شرکت
            $table->string('companyID')->nullable(); // شناسه شرکت
            $table->text('address')->nullable(); // آدرس
            $table->integer('freeCalls')->default(CUSTOMER_FREE_DRIVER_CALLS);
            $table->integer('freeLoads')->default(CUSTOMER_FREE_LOADS);
            $table->string('nationalCardImage')->nullable(); // تصویر کارت ملی
            $table->string('nationalFaceImage')->nullable(); // تصویر کارت ملی کنار چهره
            $table->string('profileImage')->nullable(); // تصویر پروفایل
            $table->string('sanaImage')->nullable(); // تصویر ثنا
            $table->string('activityLicense')->nullable(); // تصویر ثنا
            $table->boolean('userType')->default(false); // 0 = customer || 1 = bearing
            $table->dateTime('activeDate')->nullable();
            $table->string('version')->nullable();
            $table->integer('wallet')->default(0);
            $table->boolean('status')->default(1);
            $table->boolean('isAuth')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('owners');
    }
}
