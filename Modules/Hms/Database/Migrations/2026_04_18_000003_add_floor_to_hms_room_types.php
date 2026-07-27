<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFloorToHmsRoomTypes extends Migration
{
    public function up()
    {
        Schema::table('hms_room_types', function (Blueprint $table) {
            $table->unsignedBigInteger('hms_floor_id')->nullable()->after('business_id');
            $table->foreign('hms_floor_id')->references('id')->on('hms_floors')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('hms_room_types', function (Blueprint $table) {
            $table->dropForeign(['hms_floor_id']);
            $table->dropColumn('hms_floor_id');
        });
    }
}
