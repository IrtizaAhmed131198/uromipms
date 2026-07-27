<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHmsFloorsTable extends Migration
{
    public function up()
    {
        Schema::create('hms_floors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('hms_building_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('hms_building_id')->references('id')->on('hms_buildings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hms_floors');
    }
}
